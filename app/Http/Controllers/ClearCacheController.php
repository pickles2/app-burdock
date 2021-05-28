<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Project;

class ClearCacheController extends Controller
{
	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->middleware('auth');
		$this->middleware('verified');
	}

	/**
	 * Show the application dashboard.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index(Request $request, Project $project, $branch_name){
		if( !strlen($branch_name) ){
			$gitUtil = new \App\Helpers\git($project);
			$branch_name = $gitUtil->get_branch_name();
		}

		$realpath_pj_git_root = \get_project_workingtree_dir($project->project_code, $branch_name);

		return view(
			'clearcache.index',
			[
				'project' => $project,
				'branch_name' => $branch_name,
			]
		);
	}

	/**
	 * PX=clearcache を実行する
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function clearcache(Request $request, Project $project, $branch_name){
		$user_id = Auth::id();
		$burdockProjectManager = new \tomk79\picklesFramework2\burdock\projectManager\main( config('burdock.data_dir') );
		$project_branch = $burdockProjectManager->project($project->project_code)->branch($branch_name, 'preview');

		$bdAsync = new \App\Helpers\async( $project, $branch_name );
		$bdAsync->set_channel_name( urlencode($project->project_code).'----'.urlencode($branch_name).'___pxcmd-clearcache.'.$user_id );
		$bdAsync->pxcmd(
			'clearcache',
			'/',
			array()
		);

		return array(
			'result'=>true,
			'message'=>'OK',
		);

	}

}
