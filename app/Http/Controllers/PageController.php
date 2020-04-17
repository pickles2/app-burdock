<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Page;
use App\Project;

class PageController extends Controller
{
	/**
	 * 各アクションの前に実行させるミドルウェア
	 */
	public function __construct()
	{
		// ログイン・登録完了してなくても閲覧だけはできるようにexcept()で指定します。
		$this->middleware('auth');
		$this->middleware('verified');
	}

	public function index(Request $request, Project $project, $branch_name)
	{
		//
		$page_id = $request->page_id;
		$page_param = $request->page_path;
		$current = px2query(
			$project->project_code,
			$branch_name,
			'/?PX=px2dthelper.get.all&filter=false&path='.urlencode($page_id)
		);
		$current = json_decode($current);

		$editor_type = px2query(
			$project->project_code,
			$branch_name,
			'/?PX=px2dthelper.check_editor_mode&path='.urlencode($page_param)
		);
		$editor_type = json_decode($editor_type);

		return view(
			'pages.index',
			[
				'project' => $project,
				'branch_name' => $branch_name,
				'page_id' => $page_id,
				'page_param' => $page_param,
			],
			compact('current', 'editor_type')
		);
	}


	public function ajax(Request $request, Project $project, $branch_name)
	{
		$page_path = $request->path_path;
		if( !strlen($page_path) ){
			$page_path = '/';
		}
		$info = px2query(
			$project->project_code,
			$branch_name,
			$page_path.'?PX=px2dthelper.get.all'
		);
		$info = json_decode($info);

		$path = $info->page_info->path;
		$id = $info->page_info->id;

		$data = array(
			"path" => $path,
			"id" => $id,
		);
		return $data;
	}


	/**
	 * Pickles 2 Contents Editor 編集画面
	 */
	public function px2ce(Request $request, Project $project, $branch_name)
	{
		//
		$page_param = $request->page_path;
		$client_resources_dist = realpath(__DIR__.'/../../../public/assets/px2ce_resources');
		$client_resources_dist .= '/'.urlencode($project->project_code);
		if( !is_dir($client_resources_dist) ){
			mkdir($client_resources_dist);
		}
		$client_resources_dist .= '/'.urlencode($branch_name);
		if( !is_dir($client_resources_dist) ){
			mkdir($client_resources_dist);
		}

		$px2ce_client_resources = px2query(
			$project->project_code,
			$branch_name,
			'/?PX=px2dthelper.px2ce.client_resources&dist='.urlencode($client_resources_dist)
		);
		$px2ce_client_resources = json_decode($px2ce_client_resources);

		return view(
			'pages.px2ce',
			[
				'project' => $project,
				'branch_name' => $branch_name,
				'page_param' => $page_param,
			],
			compact('px2ce_client_resources')
		);
	}



	/**
	 * Pickles 2 Contents Editor の GPI
	 */
	public function px2ceGpi(Request $request, Project $project, $branch_name)
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
		file_put_contents($file, $request->data);

		$page_param = $request->page_path;
		$result = px2query(
			$project->project_code,
			$branch_name,
			$page_param.'?PX=px2dthelper.px2ce.gpi&data_filename='.urlencode($tmpFileName)
		);
		$result = json_decode($result);

		header('Content-type: text/json');
		echo json_encode($result);
		// 作成した一時ファイルを削除
		unlink($file);

		exit;
	}
}
