<?php
namespace App\Helpers;

use Illuminate\Support\Facades\Auth;
use App\Project;

class async{
	private $project;
	private $project_id;
	private $project_code;
	private $branch_name;
	private $channel_name;

	/**
	 * Constructor
	 */
	public function __construct( $project = null, $branch_name = null ){
		if( is_null($project) ){
			// Project情報に関連付けないで利用する場合
			return;
		}else if(is_object($project)){
			// Projectモデル を受け取った場合
			$this->project = $project;
			$this->project_id = $project->id;
			$this->project_code = $project->project_code;
		}else{
			// Project ID を受け取った場合
			$this->project_id = $project;
			$this->project = Project::find($project);
			$this->project_code = $project->project_code;
		}

		$this->branch_name = $branch_name;
	}


	/**
	 * ブロードキャストチャンネル名をセットする
	 */
	public function set_channel_name( $channel_name ){
		$this->channel_name = $channel_name;
	}

	/**
	 * Command を非同期に実行する
	 */
	public function cmd( $commandAry, $options = array() ){
        $user_id = Auth::id();
		$fs = new \tomk79\filesystem();

		if( strlen($this->project_code) && strlen($this->branch_name) ){
			$project_path = get_project_workingtree_dir($this->project_code, $this->branch_name);
			if( !is_dir($project_path) ){
				return false;
			}
		}

		$watchDir = env('BD_DATA_DIR').'/watcher/cmd/';
		if( !is_dir($watchDir) ){
			$fs->mkdir_r($watchDir);
		}

		$json = new \stdClass();
		$json->user_id = $user_id;
		$json->project_code = $this->project_code;
		$json->branch_name = $this->branch_name;
		$json->channel_name = $this->channel_name;

		$json->ary_command = $commandAry;
		$json->options = $options;


		// 一時ファイル名を作成
		$tmpFileName = $this->generate_filename();

		// 一時ファイルを保存
		$realpath_dir = $watchDir;
		$realpath_jsonfile = $realpath_dir.$tmpFileName;
		file_put_contents($realpath_jsonfile, json_encode($json));

		return;
	}

	/**
	 * Artisan Command を非同期に実行する
	 */
	public function artisan( $artisan_cmd, $params = array() ){
        $user_id = Auth::id();
		$fs = new \tomk79\filesystem();

		$watchDir = env('BD_DATA_DIR').'/watcher/artisan/';
		if(!is_dir($watchDir)){
			$fs->mkdir_r($watchDir);
		}

		$json = new \stdClass();
		$json->user_id = $user_id;
		$json->project_code = $this->project_code;
		$json->branch_name = $this->branch_name;
		$json->channel_name = $this->channel_name;
		$json->artisan_cmd = $artisan_cmd;
		$json->params = $params;

		// 一時ファイル名を作成
		$tmpFileName = $this->generate_filename();

		// 一時ファイルを保存
		$realpath_dir = $watchDir;
		$realpath_jsonfile = $realpath_dir.$tmpFileName;
		file_put_contents($realpath_jsonfile, json_encode($json));

		return;
	}

	/**
	 * PX Command を非同期に実行する
	 */
	public function pxcmd( $pxcommand, $path = '/', $params = array() ){
        $user_id = Auth::id();
		$fs = new \tomk79\filesystem();

		$project_path = get_project_workingtree_dir($this->project_code, $this->branch_name);
		if( !is_dir($project_path) ){
			return false;
		}
		$realpath_entry_script = $project_path.'/'.get_px_execute_path($this->project_code, $this->branch_name);
		if(!\File::exists($realpath_entry_script)) {
			return false;
		}

		$watchDir = env('BD_DATA_DIR').'/watcher/pxcmd/';
		if(!is_dir($watchDir)){
			$fs->mkdir_r($watchDir);
		}

		$json = new \stdClass();
		$json->user_id = $user_id;
		$json->project_code = $this->project_code;
		$json->branch_name = $this->branch_name;
		$json->channel_name = $this->channel_name;
		$json->entry_script = $realpath_entry_script;
		$json->path = $path;
		$json->pxcommand = $pxcommand;
		$json->params = $params;

		// 一時ファイル名を作成
		$tmpFileName = $this->generate_filename();

		// 一時ファイルを保存
		$realpath_dir = $watchDir;
		$realpath_jsonfile = $realpath_dir.$tmpFileName;
		file_put_contents($realpath_jsonfile, json_encode($json));

		return;
	}

	/**
	 * JSONファイル名を生成する
	 */
	private function generate_filename(){
		// ミリ秒を含むUnixタイムスタンプを数値（Float）で取得
		$timestamp = microtime(true);
		// ミリ秒とそうでない部分を分割
		$timeInfo = explode('.', $timestamp);
		// ミリ秒でない時間の部分を指定のフォーマットに変換し、その末尾にミリ秒を追加
		$timeWithMillisec = date('YmdHis', $timeInfo[0]).$timeInfo[1];
		// 一時ファイル名を作成
		$tmpFileName = '__tmp_'.md5($timeWithMillisec).'_data.json';
		return $tmpFileName;
	}
}
