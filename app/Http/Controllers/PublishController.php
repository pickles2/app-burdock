<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Project;
use Carbon\Carbon;
use \Zipper;

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

		$page_param = $request->page_path;
		$page_id = $request->page_id;

		$fs = new \tomk79\filesystem;

		$px2all = px2query(
			$project->project_code,
			$branch_name,
			'/?PX=px2dthelper.get.all'
		);
		$px2all = json_decode($px2all);

		$publish_log_file = $px2all->realpath_homedir.'_sys/ram/publish/publish_log.csv';
		$alert_log_file = $px2all->realpath_homedir.'_sys/ram/publish/alert_log.csv';
		$applock_file = $px2all->realpath_homedir.'_sys/ram/publish/applock.txt';

		$publish_patterns = $px2all->config->plugins->px2dt->publish_patterns;

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

		return view(
			'publish.index',
			[
				'project' => $project,
				'branch_name' => $branch_name,
				'page_param' => $page_param,
				'exists_publish_log' => $exists_publish_log,
				'exists_alert_log' => $exists_alert_log,
				'exists_applock' => $exists_applock,
				'publish_files' => $publish_files,
				'alert_files' => $alert_files,
				'diff_seconds' => $diff_seconds,
				'publish_patterns' => $publish_patterns,
			]
		);
	}


	public function publish(Request $request, Project $project, $branch_name)
	{

		$result = px2query(
			$project->project_code,
			$branch_name,
			'/?PX=publish.run'
		);
		$result = json_decode($result);

		return redirect(
			'publish/'.urlencode($project->project_code).'/'.urlencode($branch_name)
		)->with(
			'bd_flash_message', __('Publish is complete.')
		);
	}

	public function deleteApplock(Request $request, Project $project, $branch_name)
	{
		$project_code = $project->project_code;

		$px2all = px2query(
			$project->project_code,
			$branch_name,
			'/?PX=px2dthelper.get.all'
		);
		$px2all = json_decode($px2all);

		$applock_file = $px2all->realpath_homedir.'_sys/ram/publish/applock.txt';
		\File::delete($applock_file);

		if(\File::exists($applock_file)) {
			$message = 'ロックファイルを削除できませんでした。';
		} else {
			$message = 'ロックファイルを削除しました。';
		}

		return redirect(
			'publish/'.urlencode($project->project_code).'/'.urlencode($branch_name)
		)->with(
			'bd_flash_message', __($message)
		);
	}

	public function publishFileDownload(Request $request, Project $project, $branch_name)
	{
		$px2all = px2query(
			$project->project_code,
			$branch_name,
			'/?PX=px2dthelper.get.all'
		);
		$px2all = json_decode($px2all);

		$project_path = get_project_workingtree_dir($project->project_code, $branch_name);
		$publish_dir_path = $project_path.$px2all->config->path_publish_dir;

		if(\File::exists($publish_dir_path.'publish.zip')) {
			\File::delete($publish_dir_path.'publish.zip');
		}
		$files  = glob($publish_dir_path);
		Zipper::make($publish_dir_path.'publish.zip')->add($files)->close();

		return response()->download($publish_dir_path.'publish.zip');
	}

	public function publishReportDownload(Request $request, Project $project, $branch_name)
	{
		$px2all = px2query(
			$project->project_code,
			$branch_name,
			'/?PX=px2dthelper.get.all'
		);
		$px2all = json_decode($px2all);

		$publish_reports_path = $px2all->realpath_homedir.'_sys/ram/publish/';
		if(\File::exists($publish_reports_path.'publish_reports.zip')) {
			\File::delete($publish_reports_path.'publish_reports.zip');
		}
		$files = glob($publish_reports_path);
		Zipper::make($publish_reports_path.'publish_reports.zip')->add($files)->close();

		return response()->download($publish_reports_path.'publish_reports.zip');
	}
}
