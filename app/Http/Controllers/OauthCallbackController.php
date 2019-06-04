<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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

        if( $request->code ){
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
                    'verify' => false,
                ]
            );

            $res = json_decode((string)$response->getBody(), true);
            // var_dump($res);
            $data['access_token'] = $res['access_token'];



            // 取得したアクセストークンを使って、
            // ユーザー自身の情報を得てみるサンプル
            $response = $http->request(
                'get',
                env('BD_WASABI_URL').'api/user',
                [
                    'headers' => array(
                        'Accept' => 'application/json',
                        'Authorization' => 'Bearer '.$res['access_token'],
                    ),
                    'form_params' => [
                    ],
                    'verify' => false,
                ]
            );

            $res = json_decode((string)$response->getBody(), true);
            // var_dump($res);
            $data['user_info'] = $res;

        }else{
            // エラー
        }

        return view('oauth_callback.callback', ['request' => $request, 'data' => $data]);
    }
}
