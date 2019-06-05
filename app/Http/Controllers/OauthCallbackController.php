<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\User;
use App\OauthAccesskey;
use GuzzleHttp\Client as Client;

class OauthCallbackController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function wasabi_callback(Request $request)
    {
        $http = new Client;
        $data = array();

        if( !$request->code ){
            // 認証失敗、またはユーザーが認証をキャンセルした場合
            return view(
                'oauth_callback.callback',
                [
                    'request' => $request,
                    'data' => $data,
                    'error'=>array(
                        'message' => '認可コードの取得に失敗しました。',
                    )
                ]
            );
        }

        // 認証成功
        $response = $http->request(
            'post',
            env('BD_WASABI_URL').'oauth/token',
            [
                'form_params' => [
                    'grant_type' => 'authorization_code',
                    'client_id' => env('BD_WASABI_CLIENT_ID'),
                    'client_secret' => env('BD_WASABI_CLIENT_SECRET'),
                    'redirect_uri' => 'http'.($_SERVER['HTTPS']?'s':'').'://'.$_SERVER['SERVER_NAME'].'/oauth/callback/wasabi',
                    'code' => $request->code,
                ],
                'verify' => !env('APP_DEBUG'),
            ]
        );

        $res = json_decode((string)$response->getBody());
        // var_dump($res);
        $data['access_token'] = $res->access_token;

        if( !$data['access_token'] ){
            // 失敗
            return view(
                'oauth_callback.callback',
                [
                    'request' => $request,
                    'data' => $data,
                    'error'=>array(
                        'message' => 'アクセスコードの取得に失敗しました。',
                    )
                ]
            );
        }


        // 取得したアクセストークンを使って、
        // ユーザー自身の情報を得る
        $response = $http->request(
            'get',
            env('BD_WASABI_URL').'api/user',
            [
                'headers' => array(
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer '.$data['access_token'],
                ),
                'form_params' => [
                ],
                'verify' => !env('APP_DEBUG'),
            ]
        );

        $remote_user_info = json_decode((string)$response->getBody());
        // var_dump($res);
        $data['user_info'] = $remote_user_info;


        $user = Auth::user();
        if( !$user ){
            // ユーザーがログインしていない場合
            // 先に作成する
            $user_has_same_email = User::where(['email'=>$remote_user_info->email])->first();
            if( $user_has_same_email ){
                // 同じメールアドレスのユーザーが存在している
                if( !$user_has_same_email->email_verified_at ){
                    return view(
                        'oauth_callback.callback',
                        [
                            'request' => $request,
                            'data' => $data,
                            'error'=>array(
                                'message' => '同じメールアドレスが確認されていません。',
                            )
                        ]
                    );
                }

                $user = $user_has_same_email;
            }else{
                $user = new User;
                $user->id = $remote_user_info->id;
                $user->name = $remote_user_info->name;
                $user->email = $remote_user_info->email;
                $user->email_verified_at = $remote_user_info->email_verified_at;
                $user->password = '---';
                $user->lang = $remote_user_info->lang;
                $user->save();
            }
        }

        $oauth_accesskey = OauthAccesskey::where([
            'user_id' => $user->id,
            'remote_service_name' => env('BD_WASABI_URL'),
        ])->first();
        if( !$oauth_accesskey ){
            $oauth_accesskey = new OauthAccesskey();
        }
        $oauth_accesskey->user_id = $user->id;
        $oauth_accesskey->remote_service_name = env('BD_WASABI_URL');
        $oauth_accesskey->remote_user_id = $remote_user_info->id;
        $oauth_accesskey->remote_email = $remote_user_info->email;
        $oauth_accesskey->remote_lang = $remote_user_info->lang;
        $oauth_accesskey->remote_icon = $remote_user_info->icon;
        $oauth_accesskey->access_token = $data['access_token'];
        $oauth_accesskey->save();

        return view('oauth_callback.callback', ['request' => $request, 'data' => $data]);
    }
}
