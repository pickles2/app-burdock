<?php
namespace App\Helpers;

use Illuminate\Support\Facades\Auth;
use App\Project;

class async{
	private $project;
	private $project_id;
	private $project_code;
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
	 * PX Command を非同期に実行する
	 */
	public function pxcmd( $pxcommand, $params = array() ){
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
		$json->entry_script = $realpath_entry_script;
		$json->path = '/';
		$json->pxcommand = $pxcommand;
		$json->params = $params;

		// ミリ秒を含むUnixタイムスタンプを数値（Float）で取得
		$timestamp = microtime(true);
		// ミリ秒とそうでない部分を分割
		$timeInfo = explode('.', $timestamp);
		// ミリ秒でない時間の部分を指定のフォーマットに変換し、その末尾にミリ秒を追加
		$timeWithMillisec = date('YmdHis', $timeInfo[0]).$timeInfo[1];
		// 一時ファイル名を作成
		$tmpFileName = '__tmp_'.md5($timeWithMillisec).'_data.json';
		// 一時ファイルを保存
		$realpath_dir = $watchDir;
		$file = $realpath_dir.$tmpFileName;
		file_put_contents($file, json_encode($json));

		return;
	}
}
