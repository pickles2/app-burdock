<?php

namespace App\Http\Controllers\Ajax;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StorePublish;
use App\Http\Controllers\Controller;
use App\Project;
use Carbon\Carbon;

class PublishController extends Controller
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

	public function readCsvAjax(Request $request, Project $project, $branch_name)
	{

		$fs = new \tomk79\filesystem;
		$project_name = $project->project_code;
		$project_path = get_project_workingtree_dir($project_name, $branch_name);

		$px2all = px2query(
			$project->project_code,
			$branch_name,
			'/?PX=px2dthelper.get.all'
		);
		$px2all = json_decode($px2all);

		$publish_log_file = $px2all->realpath_homedir.'_sys/ram/publish/publish_log.csv';
		$alert_log_file = $px2all->realpath_homedir.'_sys/ram/publish/alert_log.csv';
		$applock_file = $px2all->realpath_homedir.'_sys/ram/publish/applock.txt';

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

		$data = array(
			"publish_log" => $publish_log,
			"alert_log" => $alert_log,
			"publish_files" => $publish_files,
			"alert_files" => $alert_files,
			'diff_seconds' => $diff_seconds,
			'exists_publish_log' => $exists_publish_log,
			'exists_alert_log' => $exists_alert_log,
			'exists_applock' => $exists_applock
		);
		return $data;
	}

	public function publishAjax(Request $request, Project $project, $branch_name)
	{
        $user_id = Auth::id();
		$publish_option = $request->publish_option;
		$paths_region = $request->paths_region;
		$paths_ignore = $request->paths_ignore;
		$keep_cache = $request->keep_cache;

		$project_code = $project->project_code;
		$project_path = get_project_workingtree_dir($project_code, $branch_name);


		$bdAsync = new \App\Helpers\async( $project, $branch_name );
		$bdAsync->set_channel_name( $project->project_code.'---'.$branch_name.'___publish.'.$user_id );
		$bdAsync->artisan(
			'bd:px2:publish',
			array(),
			array(
				'publish_option' => $publish_option,
				'paths_region' => $paths_region,
				'paths_ignore' => $paths_ignore,
				'keep_cache' => $keep_cache,
			)
		);

		/*
		// パブリッシュキューを発行する
		\App\Jobs\PublishJob::dispatch(
			$user_id,
			$project_code,
			$branch_name,
			array(
				'publish_option' => $publish_option,
				'paths_region' => $paths_region,
				'paths_ignore' => $paths_ignore,
				'keep_cache' => $keep_cache,
			)
		);
		*/


		$info = 'パブリッシュが完了しました。';

		$data = array(
			"info" => $info,
			"publish_option" => $publish_option,
			"paths_region" => $paths_region,
			"paths_ignore" => $paths_ignore,
			"keep_cache" => $keep_cache,
		);
		return $data;
	}

	public function publishCancelAjax(Request $request, Project $project, $branch_name)
	{
		//
		$project_code = $project->project_code;
		$project_path = get_project_workingtree_dir($project_code, $branch_name);
		$path_current_dir = realpath('.'); // 元のカレントディレクトリを記憶

		chdir($project_path);
		// パブリッシュのプロセスを強制終了
		$kill_info = exec('kill -USR1 '.$request->process);
		chdir($path_current_dir); // 元いたディレクトリへ戻る

		$px2all = px2query(
			$project->project_code,
			$branch_name,
			'/?PX=px2dthelper.get.all'
		);
		$px2all = json_decode($px2all);

		// applock.txtを削除
		$applock_file = $px2all->realpath_homedir.'_sys/ram/publish/applock.txt';
		\File::delete($applock_file);

		// 削除の結果をテキストで返す
		if(\File::exists($applock_file)) {
			$message = 'ロックファイルを削除できませんでした。';
		} else {
			$message = 'ロックファイルを削除しました。';
		}
		$data = array(
			"message" => $message,
		);
		return $data;
	}

	public function publishSingleAjax(Request $request, Project $project, $branch_name)
	{
		$path_region = $request->path_region;
		$px2all = px2query(
			$project->project_code,
			$branch_name,
			'/?PX=publish.run&path_region='.urlencode($path_region)
		);
		$px2all = json_decode($px2all);

		if($px2all !== false) {
			$info = 'をパブリッシュしました。';
		} else {
			$info = 'をパブリッシュできませんでした。';
		}

		$data = array(
			"info" => $info
		);
		return $data;
	}
}
