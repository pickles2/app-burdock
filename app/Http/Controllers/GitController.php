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
		if( !strlen($branch_name) ){
			$branch_name = \get_git_remote_default_branch_name($project->git_url);
		}

		$realpath_pj_git_root = \get_project_workingtree_dir($project->project_code, $branch_name);

		return view(
			'git.index',
			[
				'project' => $project,
				'branch_name' => $branch_name,
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
	 * git-pull
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function gitPull(Request $request, Project $project, $branch_name){
		$git = new \pickles2\burdock\git($project->id, $branch_name);
		$rtn = array();
		array_push($rtn, $git->git('pull origin'));
		header('Content-type: application/json');
		return json_encode($rtn);
	}

	/**
	 * git-commit
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function gitCommit(Request $request, Project $project, $branch_name){
		$git = new \pickles2\burdock\git($project->id, $branch_name);
		$rtn = array();
		array_push($rtn, $git->git('add ./'));
		$cmd_commit = 'commit -m "test commit"';
		$cmd_commit = 'commit -m '.escapeshellarg($request->commit_message);
		array_push($rtn, $git->git($cmd_commit));
		header('Content-type: application/json');
		return json_encode($rtn);
	}

	/**
	 * git-push
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function gitPush(Request $request, Project $project, $branch_name){
		$git = new \pickles2\burdock\git($project->id, $branch_name);
		$rtn = array();
		array_push($rtn, $git->git('push origin'));
		header('Content-type: application/json');
		return json_encode($rtn);
	}

}
