<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Project;

class ComposerController extends Controller
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
			'composer.index',
			[
				'project' => $project,
				'branch_name' => $branch_name,
			]
		);
	}

	/**
	 * composer-install
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function install(Request $request, Project $project, $branch_name){
		$composer = new \App\Helpers\composer($project->id, $branch_name);
		$rtn = array();
		array_push($rtn, $composer->composer('install'));
		header('Content-type: application/json');
		return json_encode($rtn);
	}

	/**
	 * composer-update
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, Project $project, $branch_name){
		$composer = new \App\Helpers\composer($project->id, $branch_name);
		$rtn = array();
		array_push($rtn, $composer->composer('update'));
		header('Content-type: application/json');
		return json_encode($rtn);
	}

}
