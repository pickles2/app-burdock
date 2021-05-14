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

	public function ajax(Request $request, $cce_id, Project $project, $branch_name)
	{
		$request_path = $request->path;
		if( $request_path == '/?PX=px2dthelper.custom_console_extensions.'.urlencode($cce_id).'.client_resources' ){
			$client_resources_dist = realpath(__DIR__.'/../../../public/assets/cce/');
			$client_resources_dist .= '/'.urlencode($cce_id);
			if( !is_dir($client_resources_dist) ){
				mkdir($client_resources_dist);
			}
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

	public function gpi(Request $request, $cce_id, Project $project, $branch_name)
	{
        $user_id = Auth::id();
		$fs = new \tomk79\filesystem();

		$current = px2query(
			$project->project_code,
			$branch_name,
			'/?PX=px2dthelper.get.all'
		);
		$current = json_decode($current);

		$watchDir = config('burdock.data_dir').'/watcher/cce/';
		if(!is_dir($watchDir.'async/'.$project->project_code.'/'.$branch_name.'/'.$cce_id.'/'.$user_id.'/')){
			$fs->mkdir_r($watchDir.'async/'.$project->project_code.'/'.$branch_name.'/'.$cce_id.'/'.$user_id.'/');
		}
		if(!is_dir($watchDir.'broadcast/'.$project->project_code.'/'.$branch_name.'/'.$cce_id.'/'.$user_id.'/')){
			$fs->mkdir_r($watchDir.'broadcast/'.$project->project_code.'/'.$branch_name.'/'.$cce_id.'/'.$user_id.'/');
		}

		$getParam = '';
		$getParam .= 'PX=px2dthelper.custom_console_extensions.'.$cce_id.'.gpi'
			.'&request='.urlencode( json_encode($request->data) )
			.'&appMode=web'
			.'&asyncMethod=file'
			.'&asyncDir='.$watchDir.'async/'.$project->project_code.'/'.$branch_name.'/'.$cce_id.'/'.$user_id.'/'
			.'&broadcastMethod=file'
			.'&broadcastDir='.$watchDir.'broadcast/'.$project->project_code.'/'.$branch_name.'/'.$cce_id.'/'.$user_id.'/'
		;


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
		file_put_contents($file, $getParam);

		$result = px2query(
			$project->project_code,
			$branch_name,
			'/?'.$getParam,
			array(
				'method' => 'post',
				'bodyFile' => $tmpFileName,
			)
		);
		$result = json_decode($result, true);

		// 作成した一時ファイルを削除
		unlink($file);

		return $result;

	}

}
