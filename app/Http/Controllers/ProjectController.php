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
		// ログイン・登録完了してなくても閲覧だけはできるようにexcept()で指定します。
		$this->middleware('auth');
		$this->middleware('verified');
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index()
	{
		//
		// 1. 新しい順に取得できない
		// $projects = Project::all();

		// 2. 記述が長くなる
		// $projects = Project::orderByDesc('created_at')->get();

		// 3. latestメソッドがおすすめ
		// ページネーション（1ページに5件表示）
		$projects = Project::latest()->paginate(5);
		// Debugbarを使ってみる
		\Debugbar::info($projects);
		return view('projects.index', ['projects' => $projects]);
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
		//
		$bd_data_dir = env('BD_DATA_DIR');
		$branch_name = 'master';

		// 記事作成時に著者のIDを保存する
		$project = new Project;
		$project->project_code = $request->project_code;
		$project->project_name = $request->project_name;
		$project->user_id = $request->user()->id;
		$project->save();

		$message = 'プロジェクトを作成しました。';
		return redirect('/')->with('my_status', __($message));
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function show(Project $project, $branch_name)
	{
		//
		$option = ' /?PX=px2dthelper.get.all';
		$bd_object = get_px_execute($project->project_code, $branch_name, $option);
		if($bd_object) {
			return view('projects.show', ['project' => $project, 'branch_name' => $branch_name], compact('bd_object'));
		} else {
			return redirect('setup/'.$project->project_code.'/'.$branch_name);
		}
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function edit(Project $project, $branch_name)
	{
		//
		// update, destroyでも同様に
		$this->authorize('edit', $project);
		return view('projects.edit', ['project' => $project, 'branch_name' => $branch_name]);
	}

	/**
	 * Update the specified resource in storage.
	 * 記事の更新を保存する
	 * @param  \App\Http\Requests\StoreProject $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function update(StoreProject $request, Project $project, $branch_name)
	{
		//
		$this->authorize('edit', $project);

		$bd_data_dir = env('BD_DATA_DIR');
		$path_current_dir = realpath('.'); // 元のカレントディレクトリを記憶

		chdir($bd_data_dir . '/projects/');
		rename($project->project_code, $request->project_code);

		$project->project_code = $request->project_code;
		$project->project_name = $request->project_name;
		$project->git_url = $request->git_url;
		$project->git_username = \Crypt::encryptString($request->git_username);
		$project->git_password = \Crypt::encryptString($request->git_password);
		$project->save();

		chdir($path_current_dir); // 元いたディレクトリへ戻る

		return redirect('projects/' . $project->project_code . '/' . $branch_name)->with('my_status', __('Updated a Project.'));
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function destroy(Request $request, Project $project, $branch_name)
	{
		//
		$page_param = $request->page_path;
		$page_id = $request->page_id;

		$project_code = $project->project_code;
		$project_path = get_project_workingtree_dir($project_code, $branch_name);

		// DBからプロジェクトを削除
		$result = $project->delete();
		// プロジェクトフォルダが存在していれば削除
		if(\File::exists(env('BD_DATA_DIR').'/projects/'.$project_code) && $result === true) {
			\File::deleteDirectory(env('BD_DATA_DIR').'/projects/'.$project_code);
			if(\File::exists(env('BD_DATA_DIR').'/projects/'.$project_code) === false) {
				$result = true;
			} else {
				$result = false;
			}
		} else {
			$result = true;
		}

		if(\File::exists(env('BD_DATA_DIR').'/projects/'.$project_code) === false && $result === true) {
			$message = 'Deleted a Project.';
		} else {
			$message = 'プロジェクトを削除できませんでした。';
		}

		return redirect('/')->with('my_status', __($message));
	}
}
