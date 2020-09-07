<?php
namespace App\Http\Controllers\GitControllerHelpers;
class GitMerge
{

	/**
	 * `git merge branchname` のフェイク処理
	 */
	public static function execute($gitUtil, $git_command_array){
		$fs = new \tomk79\filesystem();
		$trunk_branch_name = $gitUtil->get_branch_name();
		$upstream_branch_name = $git_command_array[1];

		$realpath_pj_git_trunk_branch_root = $fs->get_realpath( \get_project_workingtree_dir($gitUtil->get_project_code(), $trunk_branch_name) );
		$realpath_pj_git_upstream_branch_root = $fs->get_realpath( \get_project_workingtree_dir($gitUtil->get_project_code(), $upstream_branch_name) );

		$working_remote_name = 'burdock-working-branch';

		// --------------------------------------
		// リモートを、マージ元のブランチのディレクトリに変更する
		$result = $gitUtil->git(['remote', 'add', $working_remote_name, $realpath_pj_git_upstream_branch_root]);
		$result = $gitUtil->git(['remote', 'set-url', $working_remote_name, $realpath_pj_git_upstream_branch_root]);
		$result = $gitUtil->git(['remote', '-v']);

		// --------------------------------------
		// マージする
		$result = $gitUtil->git(['fetch', $working_remote_name]);
		$cmd_result = $gitUtil->git(['merge', $working_remote_name.'/'.$upstream_branch_name]);

		// --------------------------------------
		// マージ元の同名のブランチを削除する
		$result = $gitUtil->git(['branch', '--delete', $upstream_branch_name]);

		// --------------------------------------
		// リモートを削除
		$result = $gitUtil->git(['remote', 'rm', $working_remote_name]);

		return $cmd_result;
	}
}
