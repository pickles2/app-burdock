<?php
namespace App\Helpers\GitHelpers;
class GitInit
{

	/**
	 * `git init` のフェイク処理
	 */
	public static function execute($gitUtil, $git_command_array){
		$rtn = array(
			'stdout' => '',
			'stderr' => '',
			'return' => 0,
		);
		$stdout = '';
		$stderr = '';
		$fs = new \tomk79\filesystem();

		$realpath_pj_git_root = \get_project_workingtree_dir($gitUtil->get_project_code(), $gitUtil->get_branch_name());


		// --------------------------------------
		// Gitローカルリポジトリを初期化
		$result = $gitUtil->git(array(
			'init',
		));
		$rtn['stdout'] .= $result['stdout'];
		$rtn['stderr'] .= $result['stderr'];


		// --------------------------------------
		// 最初のブランチを作成する
		$result = $gitUtil->git(array(
			'checkout',
			'-b', $gitUtil->get_branch_name(),
		));
		$rtn['stdout'] .= $result['stdout'];
		$rtn['stderr'] .= $result['stderr'];


		// --------------------------------------
		// コミットする
		$result = $gitUtil->git(array(
			'add',
			'./',
		));
		$rtn['stdout'] .= $result['stdout'];
		$rtn['stderr'] .= $result['stderr'];

		$result = $gitUtil->git(array(
			'commit',
			'-m', 'Initial Commit.',
		));
		$rtn['stdout'] .= $result['stdout'];
		$rtn['stderr'] .= $result['stderr'];



		// --------------------------------------
		// 返す
		$cmd_result = array(
			'stdout' => trim($stdout),
			'stderr' => '',
			'return' => 0,
		);
		return $cmd_result;
	}
}
