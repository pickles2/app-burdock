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
		$burdockProjectManager = new \tomk79\picklesFramework2\burdock\projectManager\main( env('BD_DATA_DIR') );
		$project_branch = $burdockProjectManager->project($project->project_code)->branch($branch_name, 'preview');

		$pageInfoAll = $project_branch->query(
			'/?PX=clearcache'
		);

		$rtn = $pageInfoAll;
		header('Content-type: application/json');
		return json_encode($rtn);
	}

}
