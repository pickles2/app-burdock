<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Project;

class ThemeController extends Controller
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
	public function index(Request $request, Project $project, $branch_name){
		return view(
			'theme.index',
			[
				'project' => $project,
				'branch_name' => $branch_name,
			]
		);
	}

	public function ajax(Request $request, Project $project, $branch_name)
	{
		$request_path = $request->path;
		if( $request_path == '/?PX=px2dthelper.px2te.client_resources' ){
			$client_resources_dist = realpath(__DIR__.'/../../../public/assets/px2te_resources');
			$client_resources_dist .= '/'.urlencode($project->project_code);
			if( !is_dir($client_resources_dist) ){
				mkdir($client_resources_dist);
			}
			$client_resources_dist .= '/'.urlencode($branch_name);
			if( !is_dir($client_resources_dist) ){
				mkdir($client_resources_dist);
			}
			$request_path .= '&dist='.urlencode($client_resources_dist);
		}
		$info = px2query(
			$project->project_code,
			$branch_name,
			$request_path
		);
		$info = json_decode($info, true);

		return $info;
	}

	public function px2teGpi(Request $request, Project $project, $branch_name)
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
