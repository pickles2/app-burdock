<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Project;
use App\Http\Requests\StoreSitemap;

class DeliveryController extends Controller
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

		if( !strlen($project->git_url) ){
			return view(
				'delivery.index',
				[
					'error' => 'git_remot_not_set',
					'error_message' => 'Gitリモートが設定されていません。',
					'project' => $project,
					'branch_name' => $branch_name,
					'indigo_std_out' => '',
				]
			);
		}

		// parameter.phpのmk_indigo_optionsメソッド
		$parameter = $this->mk_indigo_options( $project, $branch_name );

		// load indigo\main
		$indigo = new \pickles2\indigo\main($parameter);
		$indigo_std_out = $indigo->run();


		return view(
			'delivery.index',
			[
				'error' => null,
				'error_message' => null,
				'project' => $project,
				'branch_name' => $branch_name,
				'indigo_std_out' => $indigo_std_out,
			]
		);
	}


	/**
	 * Show the application dashboard.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function indigoAjaxAPI(Request $request, Project $project, $branch_name){

		if( !strlen($project->git_url) ){
			return [
				'result' => false,
				'error' => 'git_remot_not_set',
				'error_message' => 'Gitリモートが設定されていません。',
			];
		}

		// parameter.phpのmk_indigo_optionsメソッド
		$parameter = $this->mk_indigo_options( $project, $branch_name );

		// load indigo\main
		$indigo = new \pickles2\indigo\main($parameter);
		$indigo_std_out = $indigo->ajax_run();

		return $indigo_std_out;
	}


	/**
	 * Indigoのオプションを生成する
	 */
	public function mk_indigo_options( $project, $branch_name ){
		$user = Auth::user();
		$user_id = ($user ? $user->email : null);

		$gitUtil = new \App\Helpers\git($project);
		$default_branch_name = $gitUtil->get_branch_name();
		$realpath_pj_git_root = config('burdock.data_dir').'/repositories/'.urlencode($project->project_code).'----'.urlencode($default_branch_name).'/';
		$realpath_workdir = config('burdock.data_dir').'/projects/'.urlencode($project->project_code).'/indigo/workdir/';

		$fs = new \tomk79\filesystem();
		$fs->mkdir_r($realpath_workdir);
		$fs->mkdir_r(config('burdock.data_dir').'/projects/'.urlencode($project->project_code).'/indigo/production/');

		$git_username = null;
		if( strlen($project->git_username) ){
			$git_username = \Crypt::decryptString( $project->git_username );
		}
		$git_password = null;
		if( strlen($project->git_password) ){
			$git_password = \Crypt::decryptString( $project->git_password );
		}

		$conf_connection = config('database.connections')[config('database.default')];

		$parameter = array(

			// 追加するパラメータ
			'additional_params' => array(
				'_token' => csrf_token(),
			),

			// indigo作業用ディレクトリ（絶対パス）
			'realpath_workdir' => $realpath_workdir,

			// git local のマスターデータディレクトリの絶対パス
			// 省略時は、 `realpath_workdir` 内に自動生成されます。
			'realpath_git_master_dir' => $realpath_pj_git_root,

			// リソースディレクトリ（ドキュメントルートからの相対パス）
			'relativepath_resourcedir'	=> '/common/lib-indigo/res/',

			// ajax呼出クラス（ドキュメントルートからの相対パス）
			'url_ajax_call' => '/delivery/'.urlencode($project->project_code).'/'.urlencode($branch_name).'/indigoAjaxAPI',
			
			// 画面表示上のタイムゾーン
			'time_zone' => 'Asia/Tokyo',

			// ユーザID
			'user_id' => $user_id,

			// 空間名
			'space_name' => $project->project_code,


			// DB設定
			'db' => array(
				// 'mysql' or null (nullの場合はSQLite3を使用)
				'dbms' => $conf_connection['driver'],
				'prefix' => $conf_connection['prefix'],
				'database' => $conf_connection['database'],
				'host' => $conf_connection['host'],
				'port' => $conf_connection['port'],
				'username' => $conf_connection['username'],
				'password' => $conf_connection['password'],
			),

			// 予約最大件数
			'max_reserve_record' => 10,

			// バックアップ世代管理件数
			'max_backup_generation' => 5,

			// 本番環境パス（同期先）
			'server' => array(
				array(
					// 任意の名前
					'name' => 'www1',
					// 同期先
					'dist' => config('burdock.data_dir').'/projects/'.urlencode($project->project_code).'/indigo/production/',
				),
			),

			// 同期除外ディレクトリ、またはファイル
			'ignore' => array(
				'.git'
			),

			// Git情報定義
			'git' => array(
				
				// GitリポジトリのURL
				'giturl' => $project->git_url,

				// ユーザ名
				// Gitリポジトリのユーザ名を設定。
				'username' => $git_username,

				// パスワード
				// Gitリポジトリのパスワードを設定。
				'password' => $git_password,
			)

		);
		return $parameter;
	}

}
