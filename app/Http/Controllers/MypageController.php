<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\View;
use App\User;
use App\UsersEmailChange;
use App\Http\Requests\StoreUser;
use App\Mail\UsersEmailChange as UsersEmailChangeMail;

class MypageController extends Controller
{
    /**
     * 各アクションの前に実行させるミドルウェア
     */
    public function __construct()
    {
        // ログインしなくても閲覧だけはできるようにexcept()で指定します。
        // $this->middleware('auth')->except(['index', 'show']);
        // 登録完了していなくても、退会だけはできるようにする
        // $this->middleware('verified')->except('destroy');
        $this->middleware('auth');
        $this->middleware('verified')->except(['show', 'edit', 'update', 'destroy']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        $user = Auth::user();
        return view('mypage.show', ['user' => $user]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        //
        $user = Auth::user();
        // update, destroyでも同様に
        $this->authorize('edit', $user);
        return view('mypage.edit', ['user' => $user]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'name' => (new StoreUser())->rules()['name'],
        ]);
        $user->name = $request->name;
        if( strlen($request->password) ){
            $request->validate([
                'password' => (new StoreUser())->rules()['password'],
            ]);
            $user->password = bcrypt($request->password);
        }
        $user->save();
        return redirect('mypage')->with('bd_flash_message', __('Updated a user.'));
    }

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function edit_email()
	{
		$user = Auth::user();
		return view('mypage.edit_email', ['profile' => $user]);
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function update_email(Request $request)
	{
		$user = Auth::user();
		$userStore = new StoreUser();
		$request->validate([
			'email' => $userStore->rules($user, $request->email)['email']
		]);

		// ランダムなトークンを生成
		$random_token = rand(10000, 99999).'-'.rand(1000, 9999).'-'.uniqid();

		// 同じユーザーのレコードがある場合を想定して、
		// 先に削除する
		$usersEmailChange = UsersEmailChange
			::where(['user_id'=>$user->id])
			->delete();

		$usersEmailChange = new UsersEmailChange();
		$usersEmailChange->user_id = $user->id;
		$usersEmailChange->email = $request->email;
		$usersEmailChange->token = $random_token;
		$usersEmailChange->method = $request->method;
		$usersEmailChange->created_at = date('Y-m-d H:i:s');
		$usersEmailChange->save();

		// 確認メール送信
		Mail::to($usersEmailChange->email)
			->send(new UsersEmailChangeMail($usersEmailChange));


		return redirect('mypage/edit_email_mailsent');
	}

	/**
	 * 確認メール送信後の画面
	 */
	public function update_email_mailsent(Request $request)
	{
		$user = Auth::user();
		return view('mypage.edit_email_mailsent', ['profile' => $user]);
	}

	/**
	 * 確認メールに記載のリンクを受け、完了する
	 */
	public function update_email_update(Request $request)
	{
		$user = Auth::user();

		// 同じユーザーのレコードがある場合を想定して、
		// 先に削除する
		$usersEmailChange = UsersEmailChange
			::where(['user_id'=>$user->id])
			->first();
		if( !$usersEmailChange ){
			return abort(403, '仮メールアドレスが登録されていません。');
		}
		if( $usersEmailChange->token != $request->token ){
			return abort(403, 'この操作を継続する権限がないか、トークンの有効期限が切れています。');
		}
		if( strtotime($usersEmailChange->created_at) < time() - 60*60 ){
			return abort(403, 'この操作を継続する権限がないか、トークンの有効期限が切れています。');
		}

		// 成立
		if( $usersEmailChange->method == 'backup_and_update' ){
			// 古いメールアドレスも残したまま、新しいメールアドレスをログインに使う
			$userSubEmail = new UserSubEmail();
			$userSubEmail->user_id = $user->id;
			$userSubEmail->email = $user->email;
			$userSubEmail->email_verified_at = $user->email_verified_at;
			$userSubEmail->save();

			$user->email = $usersEmailChange->email;
			$user->save();
		}elseif( $usersEmailChange->method == 'add_new' ){
			// ログインに使うメールアドレスはそのままにして、新しいメールアドレスを追加する
			$userSubEmail = new UserSubEmail();
			$userSubEmail->user_id = $user->id;
			$userSubEmail->email = $usersEmailChange->email;
			$userSubEmail->email_verified_at = date('Y-m-d H:i:s');
			$userSubEmail->save();

		}else{
			// 古いメールアドレスを上書きし、新しいメールアドレスをログインに使う (デフォルト)
			$user->email = $usersEmailChange->email;
			$user->save();
		}

		// 一時テーブルからレコードを削除する
		$usersEmailChange = UsersEmailChange
			::where(['user_id'=>$user->id])
			->delete();

		return redirect('mypage')->with('flash_message', 'メールアドレスを変更しました。');
	}

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        $user = Auth::user();
        $this->authorize('edit', $user);
        $user->delete();
        return redirect('/')->with('bd_flash_message', __('Deleted a user.'));
    }
}
