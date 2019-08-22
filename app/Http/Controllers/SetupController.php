<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Project;
use App\Setup;
use App\Http\Requests\StoreSetup;

class SetupController extends Controller
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
	public function index(Project $project, $branch_name)
	{
		//
		$fs = new \tomk79\filesystem;
		$project_name = $project->project_code;
		$project_data_path = get_project_dir($project_name);
		$project_working_tree_path = get_project_workingtree_dir($project_name, $branch_name);
		$setup_log_file = $project_data_path.'/setup_log.csv';

		if(\File::exists($setup_log_file)) {
			$exists_setup_log = true;
			$log_checked_option = $fs->read_csv($setup_log_file)[1][0];
			$log_checked_init = $fs->read_csv($setup_log_file)[1][1];
			$log_repository = $fs->read_csv($setup_log_file)[1][2];
			$log_user_name = $fs->read_csv($setup_log_file)[1][3];
			$log_password = $fs->read_csv($setup_log_file)[1][4];
			$log_setup_status = $fs->read_csv($setup_log_file)[1][5];
			$log_checked_repository = $fs->read_csv($setup_log_file)[1][6];
			$log_vendor_name = $fs->read_csv($setup_log_file)[1][7];
			$log_project_name = $fs->read_csv($setup_log_file)[1][8];
		} else {
			$exists_setup_log = false;
			$log_checked_option = false;
			$log_checked_init = false;
			$log_repository = false;
			$log_user_name = false;
			$log_password = false;
			$log_setup_status = false;
			$log_checked_repository = false;
			$log_vendor_name = false;
			$log_project_name = false;
		}

		return view('setup.index', ['project' => $project, 'branch_name' => $branch_name, 'exists_setup_log' => $exists_setup_log, 'log_checked_option' => $log_checked_option, 'log_checked_init' => $log_checked_init, 'log_repository' => $log_repository, 'log_user_name' => $log_user_name, 'log_password' => $log_password, 'log_setup_status' => $log_setup_status, 'log_checked_repository' => $log_checked_repository, 'log_vendor_name' => $log_vendor_name, 'log_project_name' => $log_project_name]);
	}

}
