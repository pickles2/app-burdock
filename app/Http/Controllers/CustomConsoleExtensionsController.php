<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Project;

class CustomConsoleExtensionsController extends Controller
{
	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->middleware('auth');
		$this->middleware('verified');
	}

	/**
	 * Show the application dashboard.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index(Request $request, $cce_id, Project $project, $branch_name){
		return view(
			'custom_console_extensions.index',
			[
				'cce_id' => $cce_id,
				'project' => $project,
				'branch_name' => $branch_name,
			]
		);
	}

	public function gpi(Request $request, $cce_id, Project $project, $branch_name)
	{
		$current = px2query(
			$project->project_code,
			$branch_name,
			'/?PX=px2dthelper.get.all'
		);
		$current = json_decode($current);

		// ミリ秒を含むUnixタイムスタンプを数値（Float）で取得
		$timestamp = microtime(true);
		// ミリ秒とそうでない部分を分割
		$timeInfo = explode('.', $timestamp);
		// ミリ秒でない時間の部分を指定のフォーマットに変換し、その末尾にミリ秒を追加
		$timeWithMillisec = date('YmdHis', $timeInfo[0]).$timeInfo[1];
		// 一時ファイル名を作成
		$tmpFileName = '__tmp_'.md5($timeWithMillisec).'_data.json';
		// 一時ファイルを保存
		$file = $current->realpath_homedir.'_sys/ram/data/'.$tmpFileName;
		file_put_contents($file, json_encode($request->data));

		$result = px2query(
			$project->project_code,
			$branch_name,
			'/?PX=px2dthelper.px2te.gpi&appMode=web&data_filename='.urlencode($tmpFileName)
		);
		$result = json_decode($result, true);

		// 作成した一時ファイルを削除
		unlink($file);

		return $result;
	}
}
