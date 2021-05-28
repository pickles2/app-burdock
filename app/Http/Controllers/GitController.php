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
			$gitUtil = new \App\Helpers\git($project);
			$branch_name = $gitUtil->get_branch_name();
		}

		$realpath_pj_git_root = \get_project_workingtree_dir($project->project_code, $branch_name);
		$error = null;
		$error_message = null;
		if( !is_dir($realpath_pj_git_root) ){
			$error = 'root_dir_not_exists';
			$error_message = 'Project root directory is not exists.';
		}elseif( !is_dir($realpath_pj_git_root.'.git/') ){
			$error = 'dotgit_dir_not_exists';
			$error_message = 'Git is not initialized.';
		}

		return view(
			'git.index',
			[
				'project' => $project,
				'branch_name' => $branch_name,
				'user' => $user,
				'error' => $error,
				'error_message' => $error_message,
			]
		);
	}

	/**
	 * git command
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function gitCommand(Request $request, Project $project, $branch_name){
		$git_command_array = $request->command_ary;

		if( count($git_command_array) == 1 && $git_command_array[0] == 'init' ){
			// `git init` のフェイク
			// この時点で .git がないので、まだ remote をセットできない。
			$gitUtil = new \App\Helpers\git($project, $branch_name);
			$rtn = \App\Helpers\GitHelpers\GitInit::execute($gitUtil, $git_command_array);
			header('Content-type: application/json');
			return json_encode($rtn);
		}

		$user_id = Auth::id();

		$bdAsync = new \App\Helpers\async( $project, $branch_name );
		$bdAsync->set_channel_name( urlencode($project->project_code).'----'.urlencode($branch_name).'___git.'.$user_id );
		$bdAsync->git(
			$git_command_array,
			array()
		);
		return array(
			'result'=>true,
			'message'=>'OK',
		);
	}

}
