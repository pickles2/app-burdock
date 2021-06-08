<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\User;
use App\Project;
use App\Http\Requests\StoreUser;

class StartpageController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
	 * はじめの画面
	 * ログイン時はダッシュボードを表示する。
     *
     * @return \Illuminate\Http\Response
     */
    public function startpage(User $user)
    {
        $user = Auth::user();

		if( !$user ){
			return view('startpage.index');
		}
		if( !$user->email_verified_at ){
			return view('auth.verify');
		}

		$projects = Project::latest()->paginate();
        return view(
            'startpage.dashboard',
            [
                'user' => $user,
                'projects' => $projects
            ]
        );
    }
}
