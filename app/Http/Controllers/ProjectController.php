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
	 * Store a newly created resource in storage.
	 * 新しい記事を保存する
	 * @param  \App\Http\Requests\StoreProject $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(StoreProject $request)
	{

		$bd_data_dir = config('burdock.data_dir');
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
	 * @param  Project $project
	 * @return \Illuminate\Http\Response
	 */
	public function edit(Project $project)
	{

		$basicauth_user_name = null;
		$realpath_preview_htpasswd = config('burdock.data_dir').'/projects/'.$project->project_code.'/preview.htpasswd';
		if( is_file($realpath_preview_htpasswd) ){
			$bin = file_get_contents($realpath_preview_htpasswd);
			$htpasswd_ary = explode(':', $bin, 2);
			$basicauth_user_name = $htpasswd_ary[0];
		}

		return view(
			'projects.edit',
			[
				'project' => $project,
				'basicauth_user_name' => $basicauth_user_name,
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

		$project->project_code = $request->project_code;
		$project->project_name = $request->project_name;
		$project->git_url = $request->git_url;
		$project->git_username = \Crypt::encryptString($request->git_username);
		if( is_object($request) && strlen($request->git_password) ){
			// 入力があった場合だけ上書き
			$project->git_password = \Crypt::encryptString( $request->git_password );
		}
		$project->git_main_branch_name = $request->git_main_branch_name;
		$project->save();


		$realpath_preview_htpasswd = config('burdock.data_dir').'/projects/'.$project->project_code.'/preview.htpasswd';
		if( strlen($request->basicauth_user_name) ){
			// --------------------------------------
			// パスワードを保存する
			$basicauth_password = $request->basicauth_password;

			// パスワードハッシュを生成する
			$hashed_passwd = $basicauth_password;
			$hash_algorithm = config('burdock.htpasswd_hash_algorithm');
			switch( $hash_algorithm ){
				case 'bcrypt':
					$hashed_passwd = password_hash($basicauth_password, PASSWORD_BCRYPT);
					break;

				case 'md5':
					$hashed_passwd = md5($basicauth_password);
					break;

				case 'sha1':
					$hashed_passwd = sha1($basicauth_password);
					break;

				case 'crypt':
					$hashed_passwd = crypt($basicauth_password, substr(crypt( trim($request->basicauth_user_name) ), -2));
					break;

				case 'plain':
				default:
					$hashed_passwd = $basicauth_password;
					break;
			}

			if( !strlen($basicauth_password) ){
				// パスワードが入力されていなければ、
				// 元のパスワードから変更しない。
				if( is_file($realpath_preview_htpasswd) ){
					$bin = file_get_contents($realpath_preview_htpasswd);
					$htpasswd_ary = explode(':', $bin, 2);
					$hashed_passwd = $htpasswd_ary[1];
				}
			}

			$src = '';
			$src .= trim($request->basicauth_user_name).':'.$hashed_passwd."\n";
			if( !file_put_contents($realpath_preview_htpasswd, $src) ){
				// TODO: エラー処理
			}

		}else{
			// --------------------------------------
			// パスワードを解除する
			if( is_file($realpath_preview_htpasswd) && !unlink($realpath_preview_htpasswd) ){
				// TODO: エラー処理
			}
		}


		// ディレクトリの処理
		$bd_data_dir = config('burdock.data_dir');

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



		// --------------------------------------
		// vhosts.conf を更新する
		$bdAsync = new \App\Helpers\async();
		$bdAsync->set_channel_name( 'system-mentenance___generate_vhosts' );
		$bdAsync->artisan(
			'bd:generate_vhosts'
		);



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
		$bd_data_dir = config('burdock.data_dir');

		$page_param = $request->page_path;
		$page_id = $request->page_id;

		$project_code = $project->project_code;

		// // プロジェクトフォルダが存在していれば削除 <- ※softDeleteに変更したため、ここでは削除しない
		// $burdockProjectManager = new \tomk79\picklesFramework2\burdock\projectManager\main( config('burdock.data_dir') );
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
