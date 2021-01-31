<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
// コントローラ内で頻繁に使うことになるので、モデルをインポートしておきます。
use App\User;
use App\Http\Requests\StoreUser;

class UserController extends Controller
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
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
     public function index()
    {
        //
        // ページネーション（1ページに5件表示）
        $users = User::paginate(5);
        return view('users.index', ['users' => $users]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // 追加用のフォーム画面へ移動
        return view('users.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreUser $request
     * @return \Illuminate\Http\Response
     */
    // 実際の追加処理
    // 終わったら、作ったばかりのユーザーのページへ移動
    public function store(StoreUser $request)
    {
        //
        $user = new User;
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = $request->password;
        $user->save();
        return redirect('users/' . $user->id)->with('bd_flash_message', __('Created new user.'));
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
        // ページネーション（1ページに5件表示）
        $user->projects = $user->projects()->paginate(5);
        return view('users.show', ['user' => $user]);
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
        // update, destroyでも同様に
        $this->authorize('edit', $user);
        return view('users.edit', ['user' => $user]);
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
    public function update(Request $request, User $user)
    {
        //
        // name欄だけを検査するため、元のStoreUserクラス内のバリデーション・ルールからname欄のルールだけを取り出す。
        $request->validate([
            'name' => (new StoreUser())->rules()['name']
        ]);
        $user->name = $request->name;
        $user->save();
        return redirect('mypage')->with('bd_flash_message', __('Updated a user.'));
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
        $this->authorize('edit', $user);
        $user->delete();
        return redirect('/')->with('bd_flash_message', __('Deleted a user.'));
    }
}
