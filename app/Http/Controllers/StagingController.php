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

		$page_param = $request->page_path;
		$page_id = $request->page_id;

		$project_name = $project->project_name;
		$project_path = get_project_workingtree_dir($project_name, $branch_name);

		$path_current_dir = realpath('.'); // 元のカレントディレクトリを記憶
		chdir($project_path);
		$data_json = shell_exec('php .px_execute.php /?PX=px2dthelper.get.all\&filter=false\&path='.$page_id);
		$current = json_decode($data_json);
		chdir($path_current_dir); // 元いたディレクトリへ戻る

		$sitemap_files = \File::files($current->realpath_homedir.'sitemaps/');
		foreach($sitemap_files as $file) {
			if($file->getExtension() === 'xlsx') {
				$get_files[] = $file;
			} else {
				$destroy_files[] = $file;
			}
		}


		$plum = new \hk\plum\main(
			array(
				// POST
				'_POST' => $_POST,

				// GET
				'_GET' => $_GET,

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
						'path' => './../repos/preview1/',
						'url' => 'http://preview1.localhost/'
					)
				),

				// Git情報定義
				'git' => array(
					
					// リポジトリのパス
					// ウェブプロジェクトのリポジトリパスを設定。
					'repository' => './../repos/master/',

					// プロトコル
					// ※現在はhttpsのみ対応
					'protocol' => 'https',

					// ホスト
					// Gitリポジトリのhostを設定。
					'host' => 'host.com',

					// GitリポジトリのURL
					// Gitリポジトリのhost以下のパスを設定。
					'url' => 'host.com/path/to.git',

					// ユーザ名
					// Gitリポジトリのユーザ名を設定。
					'username' => 'user',

					// パスワード
					// Gitリポジトリのパスワードを設定。
					'password' => 'pass'
				)
			)
		);
		$plum_std_out = $plum->run();



		return view(
			'staging.index',
			[
				'project' => $project,
				'branch_name' => $branch_name,
				'page_param' => $page_param,
				'plum_std_out' => $plum_std_out,
			],
			compact('current', 'get_files')
		);
	}
}
