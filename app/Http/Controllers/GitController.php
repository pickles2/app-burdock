<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Project;

class GitController extends Controller
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
		$user = Auth::user();

		if( !strlen($branch_name) ){
			$branch_name = \get_git_remote_default_branch_name($project->git_url);
		}

		$realpath_pj_git_root = \get_project_workingtree_dir($project->project_code, $branch_name);

		return view(
			'git.index',
			[
				'bootstrap' => 4,
				'project' => $project,
				'branch_name' => $branch_name,
				'user' => $user,
			]
		);
	}

	/**
	 * git-status
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function gitStatus(Request $request, Project $project, $branch_name){
		$git = new \pickles2\burdock\git($project->id, $branch_name);
		$rtn = array();
		array_push($rtn, $git->git('status'));
		header('Content-type: application/json');
		return json_encode($rtn);
	}

	/**
	 * git command
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function gitCommand(Request $request, Project $project, $branch_name){
		$git = new \pickles2\burdock\git($project->id, $branch_name);
		$rtn = array();
		array_push( $rtn, $git->git( $request->command_ary ) );
		header('Content-type: application/json');
		return json_encode($rtn);
	}

}
