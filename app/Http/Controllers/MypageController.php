<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
// コントローラ内で頻繁に使うことになるので、モデルをインポートしておきます。
use App\User;
use App\Http\Requests\StoreUser;

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
// 「モデル結合ルート」を利用して簡潔に記述するため、showメソッドの引数は$idではなくUser $userとします。
    public function show(User $user)
    {
        //
        $user = Auth::user();   #ログインユーザー情報を取得します。
        // ページネーション（1ページに5件表示）
        $user->projects = $user->projects()->paginate(5);
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
        $user = Auth::user();   #ログインユーザー情報を取得します。
        // update, destroyでも同様に
        $this->authorize('edit', $user);
        return view('mypage.edit', ['user' => $user]);
    }

    // 実際の更新処理
    // 終わったら、そのユーザのページへ移動
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        //
        $user = Auth::user();   #ログインユーザー情報を取得します。
        // name欄だけを検査するため、元のStoreUserクラス内のバリデーション・ルールからname欄のルールだけを取り出す。
        $request->validate([
            'name' => (new StoreUser())->rules()['name'],
            'email' => (new StoreUser())->rules()['email'], // TODO: 本来はメールアドレスの存在確認プロセスを踏むべき。
        ]);
        $user->name = $request->name;
        $user->email = $request->email; // TODO: 本来はメールアドレスの存在確認プロセスを踏むべき。
        if( strlen($request->password) ){
            $request->validate([
                'password' => (new StoreUser())->rules()['password'],
            ]);
            $user->password = bcrypt($request->password);
        }
        $user->save();
        return redirect('mypage')->with('my_status', __('Updated a user.'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        //
        $user = Auth::user();   #ログインユーザー情報を取得します。
        $this->authorize('edit', $user);
        $user->delete();
        return redirect('/')->with('my_status', __('Deleted a user.'));
    }
}
