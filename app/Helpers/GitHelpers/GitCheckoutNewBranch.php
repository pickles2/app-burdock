<?php
namespace App\Helpers\GitHelpers;
class GitCheckoutNewBranch
{

	/**
	 * `git checkout -b branchname` のフェイク処理
	 */
	public static function execute($gitUtil, $git_command_array){
		$fs = new \tomk79\filesystem();
		$current_branch_name = $gitUtil->get_branch_name();
		$new_branch_name = $git_command_array[2];

		if( !preg_match('/^[a-zA-Z0-9\_\-]+$/', $new_branch_name) ){
			// 使用されている文字をバリデート
			return array(
				'stdout' => '',
				'stderr' => 'New branch name contains invalid characters.',
				'return' => 1,
			);
		}

		$realpath_pj_git_root = $fs->get_realpath( \get_project_workingtree_dir($gitUtil->get_project_code(), $current_branch_name) );
		$realpath_pj_git_new_branch_root = $fs->get_realpath( \get_project_workingtree_dir($gitUtil->get_project_code(), $new_branch_name) );

		// ひとまず複製
		$fs->copy_r($realpath_pj_git_root, $realpath_pj_git_new_branch_root);

		$newGitUtil = new \App\Helpers\git($gitUtil->get_project_id(), $new_branch_name);
		$result = $newGitUtil->git(['checkout', 'HEAD', '--', './']);
		$result = $newGitUtil->git(['checkout', '-b', $new_branch_name]);
		$result = $newGitUtil->git(['branch', '--delete', $current_branch_name]);

		$cmd_result = array(
			'stdout' => 'Switched to a new branch \''.$new_branch_name.'\''."\n",
			'stderr' => '',
			'return' => 0,
		);
		return $cmd_result;
	}

}
