<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Project;
use App\Http\Requests\StoreProject;

class ProjectController extends Controller
{
	/**
	 * 各アクションの前に実行させるミドルウェア
	 */
	public function __construct()
	{
		// ログイン・登録完了してなくても閲覧だけはできるように except() で指定します。
		$this->middleware('auth');
		$this->middleware('verified');
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create()
	{
		//
		return view('projects.create');
	}

	/**
	 * Store a newly created resource in storage.
	 * 新しい記事を保存する
	 * @param  \App\Http\Requests\StoreProject $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(StoreProject $request)
	{

		$bd_data_dir = env('BD_DATA_DIR');
		$branch_name = 'master';

		// 記事作成時に著者のIDを保存する
		$project = new Project;
		$project->project_code = $request->project_code;
		$project->project_name = $request->project_name;
		$project->user_id = $request->user()->id;
		$project->save();

		$project_workingtree_path = get_project_workingtree_dir($project->project_code, $branch_name);
		\File::makeDirectory($project_workingtree_path, 0777, true, true);
		$project_path = get_project_dir($project->project_code);
		\File::makeDirectory($project_path, 0777, true, true);

		$message = 'プロジェクトを作成しました。';
		return redirect('/')->with('bd_flash_message', __($message));
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function edit(Project $project)
	{
		return view(
			'projects.edit',
			[
				'project' => $project,
			]
		);
	}

	/**
	 * Update the specified resource in storage.
	 * 記事の更新を保存する
	 * @param  \App\Http\Requests\StoreProject $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function update(StoreProject $request, Project $project)
	{
		$bd_data_dir = env('BD_DATA_DIR');

		if( is_dir($bd_data_dir.'/projects/'.urlencode($project->project_code)) ){
			rename(
				$bd_data_dir.'/projects/'.urlencode($project->project_code),
				$bd_data_dir.'/projects/'.urlencode($request->project_code)
			);
		}

		$fs = new \tomk79\filesystem();
		foreach( array('repositories', 'stagings') as $root_dir_name ){
			$ls = $fs->ls( $bd_data_dir.'/'.$root_dir_name.'/' );
			if( !is_array($ls) ){
				continue;
			}
			foreach( $ls as $basename ){
				if(preg_match('/^(.*?)\-\-\-(.*)$/', $basename, $matched)){
					$tmp_project_code = $matched[1];
					$tmp_branch_name = $matched[2];
					if( $tmp_project_code == $project->project_code ){
						rename(
							$bd_data_dir.'/'.$root_dir_name.'/'.$basename,
							$bd_data_dir.'/'.$root_dir_name.'/'.$request->project_code.'---'.$tmp_branch_name
						);
					}
				}
			}
		}

		$project->project_code = $request->project_code;
		$project->project_name = $request->project_name;
		$project->git_url = $request->git_url;
		$project->git_username = \Crypt::encryptString($request->git_username);
		if( is_object($request) && strlen($request->git_password) ){
			// 入力があった場合だけ上書き
			$project->git_password = \Crypt::encryptString( $request->git_password );
		}
		$project->save();

		return redirect('home/' . urlencode($project->project_code))->with('bd_flash_message', __('Updated a Project.'));
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function destroy(Request $request, Project $project)
	{
		$bd_data_dir = env('BD_DATA_DIR');

		$page_param = $request->page_path;
		$page_id = $request->page_id;

		$project_code = $project->project_code;

		// // プロジェクトフォルダが存在していれば削除 <- ※softDeleteに変更したため、ここでは削除しない
		// $burdockProjectManager = new \tomk79\picklesFramework2\burdock\projectManager\main( env('BD_DATA_DIR') );
		// $pj = $burdockProjectManager->project($project->project_code);
		// $result = $pj->delete();

		// DBからプロジェクトを削除
		$result = $project->delete();

		if( $result ){
			$message = 'プロジェクトを削除しました。';
		}else{
			$message = 'プロジェクトを削除できませんでした。データベースの更新に失敗しました。';
		}

		return redirect('/')->with('bd_flash_message', __($message));
	}
}
