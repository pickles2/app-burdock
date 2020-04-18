<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Project;

class ContentController extends Controller
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
		$page_path = $request->page_path;
		if( !strlen($page_path) ){
			$page_path = '';
		}
		$page_id = $page_path;
		if( strlen($request->page_id) ){
			$page_id = $request->page_id;
		}
		$current = px2query(
			$project->project_code,
			$branch_name,
			'/?PX=px2dthelper.get.all&filter=false&path='.urlencode($page_id)
				// ↑ここで、ページを path ではなく id で引きたいのは、
				// エイリアス、アクター、ダイナミックパスなどの実態を持たないパスを考慮しての処理。
		);
		$current = json_decode($current);
		$page_path = $current->page_info->path;


		$editor_type = px2query(
			$project->project_code,
			$branch_name,
			'/?PX=px2dthelper.check_editor_mode&path='.urlencode($page_path)
		);
		$editor_type = json_decode($editor_type);

		return view(
			'contents.index',
			[
				'project' => $project,
				'branch_name' => $branch_name,
				'page_id' => $page_id,
				'page_path' => $page_path,
			],
			compact('current', 'editor_type')
		);
	}


	public function ajax(Request $request, Project $project, $branch_name)
	{
		$page_path = $request->page_path;
		$page_path = json_decode($page_path);
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

}
