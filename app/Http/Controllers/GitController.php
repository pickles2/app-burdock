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
	 * git command
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function gitCommand(Request $request, Project $project, $branch_name){
		$git = new \pickles2\burdock\git($project->id, $branch_name);
		$rtn = array();
		$git_command_array = $request->command_ary;
		if( count($git_command_array) == 1 && $git_command_array[0] == 'branch' ){
			// `git branch` のフェイク
			array_push( $rtn, $this->gitFake_branch($git, $git_command_array) );
		}elseif( count($git_command_array) == 3 && $git_command_array[0] == 'checkout' && $git_command_array[1] == '-b' ){
			// `git checkout -b branchname` のフェイク
			array_push( $rtn, $this->gitFake_checkout_b($git, $git_command_array) );
		}elseif( count($git_command_array) == 2 && $git_command_array[0] == 'merge' ){
			// `git merge branchname` のフェイク
			array_push( $rtn, $this->gitFake_merge($git, $git_command_array) );
		}elseif( count($git_command_array) == 3 && $git_command_array[0] == 'branch' && $git_command_array[1] == '--delete' ){
			// `git branch --delete branchname` のフェイク
			array_push( $rtn, $this->gitFake_branch_delete($git, $git_command_array) );
		}else{
			array_push( $rtn, $git->git( $git_command_array ) );
		}
		// array_push( $rtn, $git->git( $git_command_array ) );
		header('Content-type: application/json');
		return json_encode($rtn);
	}

	/**
	 * `git branch` のフェイク処理
	 */
	private function gitFake_branch($git, $git_command_array){
		$fs = new \tomk79\filesystem();

		$realpath_pj_git_root = \get_project_workingtree_dir($git->get_project_code(), $git->get_branch_name());

		$filelist = $fs->ls($realpath_pj_git_root.'../');
		$stdout = '';
		foreach( $filelist as $filename ){
			if( is_dir( $realpath_pj_git_root.'../'.$filename ) ){
				$stdout .= ( $git->get_branch_name() == $filename ? '*' : ' ' ).' '.$filename."\n";
			}
		}

		$cmd_result = array(
			'stdout' => trim($stdout),
			'stderr' => '',
			'return' => 0,
		);
		return $cmd_result;
	}

	/**
	 * `git checkout -b branchname` のフェイク処理
	 */
	private function gitFake_checkout_b($git, $git_command_array){
		$fs = new \tomk79\filesystem();
		$new_branch_name = $git_command_array[2];

		$realpath_pj_git_root = $fs->get_realpath( \get_project_workingtree_dir($git->get_project_code(), $git->get_branch_name()) );
		$realpath_pj_git_new_branch_root = $fs->get_realpath( \get_project_workingtree_dir($git->get_project_code(), $new_branch_name) );

		// ひとまず複製
		$fs->copy_r($realpath_pj_git_root, $realpath_pj_git_new_branch_root);

		$newGit = new \pickles2\burdock\git($git->get_project_id(), $new_branch_name);
		$result = $newGit->git(['checkout', 'HEAD', '--', './']);
		$result = $newGit->git(['checkout', '-b', $new_branch_name]);
		$result = $newGit->git(['branch', '--delete', $git->get_branch_name()]);

		$cmd_result = array(
			'stdout' => 'Switched to a new branch \''.$new_branch_name.'\''."\n",
			'stderr' => '',
			'return' => 0,
		);
		return $cmd_result;
	}

	/**
	 * `git merge branchname` のフェイク処理
	 */
	private function gitFake_merge($git, $git_command_array){
		$fs = new \tomk79\filesystem();
		$target_branch_name = $git_command_array[1];

		$realpath_pj_git_root = $fs->get_realpath( \get_project_workingtree_dir($git->get_project_code(), $git->get_branch_name()) );
		$realpath_pj_git_target_branch_root = $fs->get_realpath( \get_project_workingtree_dir($git->get_project_code(), $target_branch_name) );

		$result = $git->git(['pull', $realpath_pj_git_target_branch_root, $target_branch_name.":".$target_branch_name]);
		// $result = $git->git(['branch']);
		$cmd_result = $git->git(['merge', $target_branch_name]);
		$result = $git->git(['branch', '--delete', $target_branch_name]);

		return $cmd_result;
	}

	/**
	 * `git branch --delete branchname` のフェイク処理
	 */
	private function gitFake_branch_delete($git, $git_command_array){
		$fs = new \tomk79\filesystem();
		$target_branch_name = $git_command_array[2];

		$realpath_pj_git_root = $fs->get_realpath( \get_project_workingtree_dir($git->get_project_code(), $git->get_branch_name()) );
		$realpath_pj_git_target_branch_root = $fs->get_realpath( \get_project_workingtree_dir($git->get_project_code(), $target_branch_name) );

		// ディレクトリごと削除
		$fs->rm($realpath_pj_git_target_branch_root);

		$cmd_result = array(
			'stdout' => 'Deleted branch '.$target_branch_name.' (was 0000000).'."\n",
			'stderr' => '',
			'return' => 0,
		);
		return $cmd_result;
	}
}
