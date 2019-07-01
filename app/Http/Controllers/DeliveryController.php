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

		// parameter.phpのmk_indigo_optionsメソッド
		$parameter = $this->mk_indigo_options( $project, $branch_name );

		// load indigo\main
		$indigo = new \indigo\main($parameter);
		$indigo_std_out = $indigo->run();


		return view(
			'delivery.index',
			[
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

		// parameter.phpのmk_indigo_optionsメソッド
		$parameter = $this->mk_indigo_options( $project, $branch_name );

		// load indigo\main
		$indigo = new \indigo\ajax($parameter);
		$indigo_std_out = $indigo->ajax_run();

		return $indigo_std_out;
	}


	/**
	 * Indigoのオプションを生成する
	 */
	private function mk_indigo_options( $project, $branch_name ){
		$user = Auth::user();

		$parameter = array(

			// 追加するパラメータ
			'additional_params' => array(
				'_token' => csrf_token(),
			),

			// indigo作業用ディレクトリ（絶対パス）
			'realpath_workdir' => env('BD_DATA_DIR').'/projects/'.urlencode($project->project_code).'/indigo/workdir/',

			// リソースディレクトリ（ドキュメントルートからの相対パス）
			'relativepath_resourcedir'	=> '/common/lib-indigo/res/',

			// ajax呼出クラス（ドキュメントルートからの相対パス）
			'realpath_ajax_call' => '/delivery/'.urlencode($project->project_code).'/'.urlencode($branch_name).'/indigoAjaxAPI',
			
			// 画面表示上のタイムゾーン
			'time_zone' => 'Asia/Tokyo',

			// ユーザID
			'user_id' => $user->id,

			// DB設定
			'db' => array(
				// 'mysql' or null (nullの場合はSQLite3を使用)
				// ※バージョン0.1.0時点ではmysql未対応
				'db_type' => null,
			),

			// 予約最大件数
			'max_reserve_record' => 10,

			// バックアップ世代管理件数　※バージョン0.1.0時点では未対応
			'max_backup_generation' => 5,

			// 本番環境パス（同期先）※バージョン0.1.0時点では先頭の設定内容のみ有効
			'server' => array(
				array(
					// 任意の名前
					'name' => 'www1',
					// 同期先絶対パス
					'real_path' => env('BD_DATA_DIR').'/projects/'.urlencode($project->project_code).'/indigo/production/',
				),
			),

			// 同期除外ディレクトリ、またはファイル
			'ignore' => array(
				'.git',
				'.htaccess'
			),

			// Git情報定義
			'git' => array(
				
				// GitリポジトリのURL
				'giturl' => $project->git_url,

				// ユーザ名
				// Gitリポジトリのユーザ名を設定。
				'username' => \Crypt::decryptString( $project->git_username ),

				// パスワード
				// Gitリポジトリのパスワードを設定。
				'password' => \Crypt::decryptString( $project->git_password ),
			)

		);
		return $parameter;
	}

}
