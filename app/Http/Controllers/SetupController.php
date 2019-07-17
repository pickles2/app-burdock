<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Project;
use App\Setup;
use App\Http\Requests\StoreSetup;

class SetupController extends Controller
{
	/**
	 * 各アクションの前に実行させるミドルウェア
	 */
	public function __construct()
	{
		// ログイン・登録完了してなくても閲覧だけはできるようにexcept()で指定します。
		$this->middleware('auth');
		$this->middleware('verified');
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index(Project $project, $branch_name)
	{
		//
		
		return view('setup.index', ['project' => $project, 'branch_name' => $branch_name]);
	}

}
