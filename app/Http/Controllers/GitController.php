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
			$gitUtil = new \pickles2\burdock\git($project);
			$branch_name = $gitUtil->get_branch_name();
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
	 * git command
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function gitCommand(Request $request, Project $project, $branch_name){
		$gitUtil = new \pickles2\burdock\git($project, $branch_name);
		$rtn = array();
		$git_command_array = $request->command_ary;

		$gitUtil->set_remote_origin();
		$gitUtil->git( ['fetch'] );

		if( count($git_command_array) == 2 && $git_command_array[0] == 'branch' && $git_command_array[1] == '-a' ){
			// `git branch -a` のフェイク
			// ブランチの一覧を取得する
			array_push( $rtn, GitControllerHelpers\GitBranch::execute($gitUtil, $git_command_array) );
		}elseif( count($git_command_array) == 3 && $git_command_array[0] == 'checkout' && $git_command_array[1] == '-b' ){
			// `git checkout -b branchname` のフェイク
			// カレントブランチから新しいブランチを作成する
			array_push( $rtn, GitControllerHelpers\GitCheckoutNewBranch::execute($gitUtil, $git_command_array) );
		}elseif( count($git_command_array) == 4 && $git_command_array[0] == 'checkout' && $git_command_array[1] == '-b' ){
			// `git checkout -b localBranchname remoteBranch` のフェイク
			// リモートブランチをチェックアウトする
			array_push( $rtn, GitControllerHelpers\GitCheckoutRemoteBranch::execute($gitUtil, $git_command_array) );
		}elseif( count($git_command_array) == 2 && $git_command_array[0] == 'merge' && !preg_match('/^remotes\//', $git_command_array[1]) ){
			// `git merge branchname` のフェイク
			// マージする
			// ただし、ここを通過するのはマージ元がローカルブランチの場合のみ。リモートブランチからのマージする場合はフェイクは要らない。
			array_push( $rtn, GitControllerHelpers\GitMerge::execute($gitUtil, $git_command_array) );
		}elseif( count($git_command_array) == 3 && $git_command_array[0] == 'branch' && $git_command_array[1] == '--delete' ){
			// `git branch --delete branchname` のフェイク
			// ブランチを削除する
			array_push( $rtn, GitControllerHelpers\GitBranchDelete::execute($gitUtil, $git_command_array) );
		}else{
			array_push( $rtn, $gitUtil->git( $git_command_array ) );
		}

		$gitUtil->clear_remote_origin();

		// array_push( $rtn, $gitUtil->git( $git_command_array ) );
		header('Content-type: application/json');
		return json_encode($rtn);
	}

}
