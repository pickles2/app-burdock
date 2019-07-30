<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Project;
use Carbon\Carbon;

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

		$fs = new \tomk79\filesystem;
		$project_name = $project->project_code;
		$project_path = get_project_workingtree_dir($project_name, $branch_name);
		$publish_log_file = $project_path.'/'.get_path_homedir($project->project_code, $branch_name).'_sys/ram/publish/publish_log.csv';
		$alert_log_file = $project_path.'/'.get_path_homedir($project->project_code, $branch_name).'_sys/ram/publish/alert_log.csv';
		$applock_file = $project_path.'/'.get_path_homedir($project->project_code, $branch_name).'_sys/ram/publish/applock.txt';

		$option = ' /?PX=px2dthelper.get.all';
		$bd_object = get_px_execute($project->project_code, $branch_name, $option);
		$publish_patterns = $bd_object->config->plugins->px2dt->publish_patterns;

		if(\File::exists($publish_log_file)) {
			$exists_publish_log = true;
			$publish_log = $fs->read_csv($publish_log_file);
			$publish_files = count($publish_log) - 1;
		} else {
			$exists_publish_log = false;
			$publish_log = false;
			$publish_files = 0;
		}

		if(\File::exists($alert_log_file)) {
			$exists_alert_log = true;
			$alert_log = $fs->read_csv($alert_log_file);
			$alert_files = count($alert_log) - 1;
		} else {
			$exists_alert_log = false;
			$alert_log = false;
			$alert_files = 0;
		}

		if(\File::exists($applock_file)) {
			$exists_applock = true;
		} else {
			$exists_applock = false;
		}

		if($publish_log) {
			$dt1 = new Carbon($publish_log[array_key_last($publish_log)][0]);
			$dt2 = new Carbon($publish_log[1][0]);
			$diff_seconds = $dt1->diffInSeconds($dt2);
		} else {
			$diff_seconds = 0;
		}

		return view('publish.index', ['project' => $project, 'branch_name' => $branch_name, 'page_param' => $page_param, 'exists_publish_log' => $exists_publish_log, 'exists_alert_log' => $exists_alert_log, 'exists_applock' => $exists_applock, 'publish_files' => $publish_files, 'alert_files' => $alert_files, 'diff_seconds' => $diff_seconds, 'publish_patterns' => $publish_patterns] );
	}

	//
	public function publish(Request $request, Project $project, $branch_name)
	{
		//
		$option = ' /?PX=publish.run';
		get_px_execute($project->project_code, $branch_name, $option);

		return redirect('publish/' . $project->project_code . '/' . $branch_name)->with('my_status', __('Publish is complete.'));
	}

	public function deleteApplock(Request $request, Project $project, $branch_name)
	{
		//
		$project_code = $project->project_code;
		$project_path = get_project_workingtree_dir($project_code, $branch_name);
		$applock_file = $project_path.'/'.get_path_homedir($project->project_code, $branch_name).'_sys/ram/publish/applock.txt';
		\File::delete($applock_file);

		if(\File::exists($applock_file)) {
			$message = 'ロックファイルを削除できませんでした。';
		} else {
			$message = 'ロックファイルを削除しました。';
		}

		return redirect('publish/' . $project->project_code . '/' . $branch_name)->with('my_status', __($message));
	}
}
