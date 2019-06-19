<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Project;

class PublishController extends Controller
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
		$page_param = $request->page_path;
		$page_id = $request->page_id;

		$project_path = get_project_workingtree_dir($project->project_code, $branch_name);

		return view('publish.index', ['project' => $project, 'branch_name' => $branch_name, 'page_param' => $page_param] );
	}

	//
	public function publish(Request $request, Project $project, $branch_name)
	{
		//
		$project_path = get_project_workingtree_dir($project->project_code, $branch_name);
		$path_current_dir = realpath('.'); // 元のカレントディレクトリを記憶

		chdir($project_path);
		shell_exec('php .px_execute.php /?PX=publish.run');

		chdir($path_current_dir); // 元いたディレクトリへ戻る

		return redirect('publish/' . $project->project->project_code . '/' . $branch_name)->with('my_status', __('Publish is complete.'));
	}
}
