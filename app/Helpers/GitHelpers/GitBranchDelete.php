<?php
namespace App\Helpers\GitHelpers;
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

		// .git の中に 444 (r--r--r--) となるファイルが生成されている事があるので、
		// 削除を受け付けるパーミッションを変更しておく。
		$result = $fs->chmod_r($realpath_pj_git_target_branch_root, 0777);

		// ディレクトリごと削除
		$fs->chmod_r( $realpath_pj_git_target_branch_root, 0777 );
		$result = $fs->rm($realpath_pj_git_target_branch_root);



		// --------------------------------------
		// vhosts.conf を更新する
		$bdAsync = new \App\Helpers\async();
		$bdAsync->set_channel_name( 'system-mentenance___generate_vhosts' );
		$bdAsync->artisan(
			'bd:generate_vhosts'
		);



		$cmd_result = array(
			'stdout' => 'Deleted branch '.$target_branch_name.' (was 0000000).'."\n",
			'stderr' => '',
			'return' => 0,
		);
		return $cmd_result;
	}
}
