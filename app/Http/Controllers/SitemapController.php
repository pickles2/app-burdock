<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Project;
use App\Http\Requests\StoreSitemap;

class SitemapController extends Controller
{
	//
	/**
	 * 各アクションの前に実行させるミドルウェア
	 */
	public function __construct()
	{
		// ログイン・登録完了してなくても閲覧だけはできるようにexcept()で指定します。
		$this->middleware('auth');
		$this->middleware('verified');
	}



	/**
	 * サイトマップファイルの一覧画面
	 */
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

		$sitemap_files = \File::files($current->realpath_homedir.'sitemaps/');
		$get_files = array();
		$destroy_files = array();
		foreach($sitemap_files as $file) {
			$filename = preg_replace( '/\..*?$/', '', $file->getFilename() );
			if( !array_key_exists($filename, $get_files) ){
				$get_files[$filename] = array();
				$get_files[$filename]['filename'] = $filename;
				$get_files[$filename]['basename'] = null;
				$get_files[$filename]['extensions'] = array();
			}
			$ext = strtolower($file->getExtension());
			if( $ext == 'csv' ){
				$get_files[$filename]['basename'] = $file->getFilename();
			}
			$get_files[$filename]['extensions'][$ext] = array(
				'ext' => $ext,
				'basename' => $file->getFilename(),
			);
		}

		return view(
			'sitemaps.index',
			[
				'project' => $project,
				'branch_name' => $branch_name,
				'page_param' => $page_param
			],
			compact('current', 'get_files')
		);
	}



	/**
	 * アップロードされたサイトマップファイルを保存する
	 */
	public function upload(StoreSitemap $request, Project $project, $branch_name)
	{
		$current = px2query(
			$project->project_code,
			$branch_name,
			'/?PX=px2dthelper.get.all'
		);
		$current = json_decode($current);
		$upload_file = $request->file;
		$old_file = $upload_file;
		$mimetype = $upload_file->clientExtension();
		$file_name = $upload_file->getClientOriginalName();
		$new_file = $current->realpath_homedir.'sitemaps/'.$file_name;

		if (!copy($old_file, $new_file)) {
			$message = 'Could not update Sitemap.';
		} else {
			$message = 'Updated a Sitemap.';
		}

		return redirect('sitemaps/'.urlencode($project->project_code).'/'.urlencode($branch_name))->with('my_status', __($message));
	}



	/**
	 * サイトマップファイルをダウンロードする
	 */
	public function download(Request $request, Project $project, $branch_name)
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

		if($request->file === 'csv') {
			// CSVファイルのダウンロード
			$file_name = $request->file_name;
			$csv_file_name = str_replace('xlsx', 'csv', $file_name);
			$pathToFile = $current->realpath_homedir.'sitemaps/'.$csv_file_name;
			$name = $csv_file_name;

			return response()->download($pathToFile, $name);
		} elseif($request->file === 'xlsx') {
			// XLSXファイルのダウンロード
			$xlsx_file_name = $request->file_name;
			$pathToFile = $current->realpath_homedir.'sitemaps/'.$xlsx_file_name;
			$name = $xlsx_file_name;

			return response()->download($pathToFile, $name);
		}
	}


	/**
	 * サイトマップファイルの削除を実行する
	 */
	public function destroy(Request $request, Project $project, $branch_name)
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

		$base_file_name = $request->file_name;
		$base_file_name = preg_replace('/\.[a-zA-Z0-9]+$/', '', $base_file_name);
		$xlsx_file_name = $base_file_name.'.xlsx';
		$csv_file_name = $base_file_name.'.csv';
		$xlsx_file_path = $current->realpath_homedir.'sitemaps/'.$xlsx_file_name;
		$csv_file_path = $current->realpath_homedir.'sitemaps/'.$csv_file_name;

		\File::delete($xlsx_file_path, $csv_file_path);
		clearstatcache();

		if(\File::exists($xlsx_file_path) === false && \File::exists($csv_file_path) === false) {
			$message = $xlsx_file_name.'と'.$csv_file_name.'を削除しました。';
		} else {
			$message = 'サイトマップを削除できませんでした。';
		}

		return redirect('sitemaps/'.urlencode($project->project_code).'/'.urlencode($branch_name))->with('my_status', __($message));
	}
}
