<?php
namespace App\Http\Controllers\GitControllerHelpers;
class GitMerge
{

	/**
	 * `git merge branchname` のフェイク処理
	 */
	public static function execute($gitUtil, $git_command_array){
		$fs = new \tomk79\filesystem();
		$target_branch_name = $git_command_array[1];

		$realpath_pj_git_root = $fs->get_realpath( \get_project_workingtree_dir($gitUtil->get_project_code(), $gitUtil->get_branch_name()) );
		$realpath_pj_git_target_branch_root = $fs->get_realpath( \get_project_workingtree_dir($gitUtil->get_project_code(), $target_branch_name) );

		$result = $gitUtil->git(['pull', $realpath_pj_git_target_branch_root, $target_branch_name.":".$target_branch_name]);
		// $result = $gitUtil->git(['branch']);
		$cmd_result = $gitUtil->git(['merge', $target_branch_name]);
		$result = $gitUtil->git(['branch', '--delete', $target_branch_name]);

		return $cmd_result;
	}
}
