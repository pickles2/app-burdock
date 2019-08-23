<?php
namespace pickles2\burdock;

use App\Project;

class git{
	private $project;
	private $project_id;
	private $branch_name;

	/**
	 * Constructor
	 */
	public function __construct( $project_id, $branch_name ){
		$this->project_id = $project_id;
		$this->project = Project::find($project_id);

		if( !strlen($branch_name) ){
			$branch_name = \get_git_remote_default_branch_name($project->git_url);
		}
		$this->branch_name = $branch_name;
	}

	/**
	 * プロジェクトIDを取得する
	 */
	public function get_project_id(){
		return $this->project_id;
	}

	/**
	 * プロジェクトCodeを取得する
	 */
	public function get_project_code(){
		return $this->project->project_code;
	}

	/**
	 * ブランチ名を取得する
	 */
	public function get_branch_name(){
		return $this->branch_name;
	}

	/**
	 * Gitコマンドを実行する
	 */
	public function git( $git_sub_command ){
		if( !is_array($git_sub_command) ){
			return array(
				'stdout' => '',
				'stderr' => 'Internal Error: Invalid arguments are given.',
				'return' => 1,
			);
		}

		if( !$this->is_valid_command($git_sub_command) ){
			return array(
				'stdout' => '',
				'stderr' => 'Internal Error: Command not permitted.',
				'return' => 1,
			);
		}

		foreach($git_sub_command as $idx=>$git_sub_command_row){
			$git_sub_command[$idx] = escapeshellarg($git_sub_command_row);
		}
		$cmd = implode(' ', $git_sub_command);

		$realpath_pj_git_root = \get_project_workingtree_dir($this->project->project_code, $this->branch_name);

		$cd = realpath('.');
		chdir($realpath_pj_git_root);

		ob_start();
		$proc = proc_open('git '.$cmd, array(
			0 => array('pipe','r'),
			1 => array('pipe','w'),
			2 => array('pipe','w'),
		), $pipes);

		$io = array();
		foreach($pipes as $idx=>$pipe){
			$io[$idx] = stream_get_contents($pipe);
			fclose($pipe);
		}
		$return_var = proc_close($proc);
		ob_get_clean();

		chdir($cd);

		return array(
			'stdout' => $this->conceal_confidentials($io[1]),
			'stderr' => $this->conceal_confidentials($io[2]),
			'return' => $return_var,
		);
	}

	/**
	 * Gitコマンドに不正がないか確認する
	 */
	private function is_valid_command( $git_sub_command ){

		if( !is_array($git_sub_command) ){
			// 配列で受け取る
			return false;
		}

		// 許可されたコマンド
		switch( $git_sub_command[0] ){
			case 'config':
			case 'status':
			case 'branch':
			case 'log':
			case 'show':
			case 'remote':
			case 'checkout':
			case 'add':
			case 'commit':
			case 'merge':
			case 'push':
			case 'pull':
				break;
			default:
				return false;
				break;
		}

		// 不正なオプション
		foreach( $git_sub_command as $git_sub_command_row ){
			if( preg_match( '/^\-\-output(?:\=.*)?$/', $git_sub_command_row ) ){
				return false;
			}
		}

		return true;
	}

	/**
	 * gitコマンドの結果から、秘匿な情報を隠蔽する
	 * @param string $str 出力テキスト
	 * @return string 秘匿情報を隠蔽加工したテキスト
	 */
	private function conceal_confidentials($str){

		// gitリモートリポジトリのURLに含まれるパスワードを隠蔽
		// ただし、アカウント名は残す。
		$str = preg_replace('/((?:[a-zA-Z\-\_]+))\:\/\/([^\s\/\\\\]*?\:)([^\s\/\\\\]*)\@/si', '$1://$2xxxxxxxxxxxx@', $str);

		return $str;
	}
}
