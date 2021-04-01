<?php
namespace App\Http\Controllers\GitControllerHelpers;
class GitInit
{

	/**
	 * `git init` のフェイク処理
	 */
	public static function execute($gitUtil, $git_command_array){
		$stdout = '';
		$fs = new \tomk79\filesystem();

		$realpath_pj_git_root = \get_project_workingtree_dir($gitUtil->get_project_code(), $gitUtil->get_branch_name());


		// Gitローカルリポジトリを初期化
		$result = $gitUtil->git(array('init'));





		// 返す
		$cmd_result = array(
			'stdout' => trim($stdout),
			'stderr' => '',
			'return' => 0,
		);
		return $cmd_result;
	}
}
