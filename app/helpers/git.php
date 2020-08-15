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
	public function __construct( $project = null, $branch_name = null ){
		if(is_null($project)){
			// Project情報に関連付けないで利用する場合
			return;
		}else if(is_object($project)){
			// Projectモデル を受け取った場合
			$this->project = $project;
			$this->project_id = $project->id;
		}else{
			// Project ID を受け取った場合
			$this->project_id = $project;
			$this->project = Project::find($project);
		}

		if( !strlen($branch_name) ){
			$branch_name = $this->get_remote_default_branch_name($project->git_url);
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
	 * Gitリモートサーバーからデフォルトのブランチ名を取得する
	 */
	public function get_remote_default_branch_name( $git_url = null ) {
		$default = 'master';
		if( !strlen( $git_url ) ){
			$git_url = $this->project->git_url;
		}
		if( !strlen( $git_url ) ){
			return $default;
		}
		$result = shell_exec('git ls-remote --symref '.escapeshellarg($git_url).' HEAD');
		if(!is_string($result) || !strlen($result)){
			return $default;
		}

		if( !preg_match('/^ref\: refs\/heads\/([^\s]+)\s+HEAD/', $result, $matched) ){
			return $default;
		}

		return $matched[1];
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
			if($idx){
				$io[$idx] = stream_get_contents($pipe);
			}
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
	 * URLに認証情報を埋め込む
	 */
	private function url_bind_confidentials($url, $user_name, $password){
		$parsed_git_url = parse_url($url);
		$rtn = '';
		$rtn .= $parsed_git_url['scheme'].'://';
		$rtn .= urlencode($user_name);
		$rtn .= ':'.urlencode($password);
		$rtn .= '@';
		$rtn .= $parsed_git_url['host'];
		if( array_key_exists('port', $parsed_git_url) && strlen($parsed_git_url['port']) ){
			$rtn .= ':'.$parsed_git_url['port'];
		}
		$rtn .= $parsed_git_url['path'];
		if( array_key_exists('query', $parsed_git_url) && strlen($parsed_git_url['query']) ){
			$rtn .= '?'.$parsed_git_url['query'];
		}
		return $rtn;
	}

	/**
	 * gitコマンドの結果から、秘匿な情報を隠蔽する
	 * @param string $str 出力テキスト
	 * @return string 秘匿情報を隠蔽加工したテキスト
	 */
	private function conceal_confidentials($str){

		// gitリモートリポジトリのURLに含まれるパスワードを隠蔽
		// ただし、アカウント名は残す。
		$str = preg_replace('/((?:[a-zA-Z\-\_]+))\:\/\/([^\s\/\\\\]*?\:)([^\s\/\\\\]*)\@/si', '$1://$2********@', $str);

		return $str;
	}
}
