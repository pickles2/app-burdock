<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Project;
use App\Http\Requests\StoreSitemap;

class StagingController extends Controller
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

		$default_branch_name = get_git_remote_default_branch_name();

		$fs = new \tomk79\filesystem();

		$realpath_pj_git_root = env('BD_DATA_DIR').'/repositories/'.urlencode($project->project_code).'---'.urlencode($default_branch_name).'/';
		$fs->mkdir_r($realpath_pj_git_root);
		$fs->mkdir_r(env('BD_DATA_DIR').'/stagings/');

		$preview_server = array();
		for( $i = 1; $i <= 10; $i ++ ){
			array_push($preview_server, array(
				'name' => 'stg'.$i.'',
				'path' => env('BD_DATA_DIR').'/stagings/'.urlencode($project->project_code).'---stg'.$i.'/',
				'url' => 'http'.($_SERVER["HTTPS"] ? 's' : '').'://'.urlencode($project->project_code).'---stg'.$i.'.'.env('BD_PLUM_STAGING_DOMAIN').'/',
			));
		}

		$git_username = null;
		if( strlen($project->git_username) ){
			$git_username = \Crypt::decryptString( $project->git_username );
		}
		$git_password = null;
		if( strlen($project->git_password) ){
			$git_password = \Crypt::decryptString( $project->git_password );
		}


		$plum = new \hk\plum\main(
			array(

				// 追加するパラメータ
				'additional_params' => array(
					'_token' => csrf_token(),
				),

				// プレビューサーバ定義
				'preview_server' => $preview_server,

				// Git情報定義
				'git' => array(
					
					// リポジトリのパス
					// ウェブプロジェクトのリポジトリパスを設定。
					'repository' => $realpath_pj_git_root,

					// GitリポジトリのURL
					'url' => $project->git_url,

					// ユーザ名
					// Gitリポジトリのユーザ名を設定。
					'username' => $git_username,

					// パスワード
					// Gitリポジトリのパスワードを設定。
					'password' => $git_password,
				)
			)
		);

		$plum_std_out = $plum->run();


		return view(
			'staging.index',
			[
				'project' => $project,
				'branch_name' => $branch_name,
				'plum_std_out' => $plum_std_out,
			]
		);
	}
}
