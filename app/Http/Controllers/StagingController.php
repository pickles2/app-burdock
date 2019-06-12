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

        $realpath_pj_git_root = './../bd_data/projects/'.urlencode($project->project_name).'/stagings/master/';

		$plum = new \hk\plum\main(
			array(

				'additional_params' => array(
					'_token' => csrf_token(),
				),

				// プレビューサーバ定義
				'preview_server' => array(

					// プレビューサーバの数だけ設定する
					//
					//   string 'name':
					//     - プレビューサーバ名(任意)
					//   string 'path':
					//     - プレビューサーバ(デプロイ先)のパス
					//   string 'url':
					//     - プレビューサーバのURL
					//       Webサーバのvirtual host等で設定したURL
					//
					array(
						'name' => 'preview1',
						'path' => './../bd_data/projects/'.urlencode($project->project_name).'/stagings/previews/preview1/',
						'url' => 'https://preview1.'.urlencode($project->id).'.burdock.localhost/'
					),
					array(
						'name' => 'preview2',
						'path' => './../bd_data/projects/'.urlencode($project->project_name).'/stagings/previews/preview2/',
						'url' => 'https://preview2.'.urlencode($project->id).'.burdock.localhost/'
					),
				),

				// Git情報定義
				'git' => array(
					
					// リポジトリのパス
					// ウェブプロジェクトのリポジトリパスを設定。
					'repository' => $realpath_pj_git_root,

					// GitリポジトリのURL
					'url' => $project->git_url,

					// ユーザ名
					// Gitリポジトリのユーザ名を設定。
					'username' => \Crypt::decryptString( $project->git_username ),

					// パスワード
					// Gitリポジトリのパスワードを設定。
					'password' => \Crypt::decryptString( $project->git_password ),
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
			],
			compact('current', 'get_files')
		);
	}
}
