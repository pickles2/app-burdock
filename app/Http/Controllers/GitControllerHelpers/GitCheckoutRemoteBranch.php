<?php
namespace App\Http\Controllers\GitControllerHelpers;
class GitCheckoutRemoteBranch
{

	/**
	 * `git checkout -b localBranchname remoteBranchname` のフェイク処理
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

		// ひとまず空のディレクトリを作成
		$fs->mkdir_r($realpath_pj_git_new_branch_root);


		// ブランチ名指定でcloneする
		$newGitUtil = new \pickles2\burdock\git( $gitUtil->get_project_id(), $new_branch_name );
		$git_remote = $newGitUtil->url_bind_confidentials();
		$result = $newGitUtil->git(['clone', '-b', $new_branch_name, $git_remote, './']);
		$newGitUtil->clear_remote_origin();


		$newComposer = new \pickles2\burdock\composer( $gitUtil->get_project_id(), $new_branch_name );
		$newComposer->composer(['install']);


		$cmd_result = array(
			'stdout' => 'Switched to a new branch \''.$new_branch_name.'\''."\n",
			'stderr' => '',
			'return' => 0,
		);
		return $cmd_result;
	}

}
