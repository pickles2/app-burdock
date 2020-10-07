<?php
namespace App\Http\Controllers\GitControllerHelpers;
class GitBranchDelete
{

	/**
	 * `git branch --delete branchname` のフェイク処理
	 */
	public static function execute($gitUtil, $git_command_array){
		$fs = new \tomk79\filesystem();
		$target_branch_name = $git_command_array[2];

		$realpath_pj_git_root = $fs->get_realpath( \get_project_workingtree_dir($gitUtil->get_project_code(), $gitUtil->get_branch_name()) );
		$realpath_pj_git_target_branch_root = $fs->get_realpath( \get_project_workingtree_dir($gitUtil->get_project_code(), $target_branch_name) );

		// ディレクトリごと削除
		$result = $fs->rm($realpath_pj_git_target_branch_root);

		$cmd_result = array(
			'stdout' => 'Deleted branch '.$target_branch_name.' (was 0000000).'."\n",
			'stderr' => '',
			'return' => 0,
		);
		return $cmd_result;
	}
}
