<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\User;
use App\Project;
use App\Http\Requests\StoreUser;
use App\Setup;
use App\Http\Requests\StoreSetup;

class HomeController extends Controller
{
	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->middleware('auth');
		$this->middleware('verified');
	}

	/**
	 * Show the application dashboard.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index(Project $project, $branch_name)
	{

		$burdockProjectManager = new \tomk79\picklesFramework2\burdock\projectManager\main( env('BD_DATA_DIR') );
		$project_branch = $burdockProjectManager->project($project->project_code)->branch($branch_name, 'preview');
		$project_status = $project_branch->status();

		if ($project_status->isPxStandby) {
			// --------------------------------------
			// セットアップは正常に完了しているとき
			$bd_object = px2query(
				$project->project_code,
				$branch_name,
				'/?PX=px2dthelper.get.all'
			);
			$bd_object = json_decode($bd_object);

			return view('home.index', [
				'project' => $project,
				'branch_name' => $branch_name,
				'project_status' => $project_status,
			], compact('bd_object'));

		} elseif (session('my_status')) {
			// TODO: このパターンは何のときに通る？
			// $message = session('my_status');
			// return redirect('setup/'.$project->project_code.'/'.$branch_name)->with('my_status', __($message));
			trigger_error( 'セットアップは完了していません。 my_status がセットされています。' );
			return;
		} else {
			return $this->setup($project, $branch_name);
		}
	}

	/**
	 * プロジェクトの初期セットアップを実行する
	 */
	private function setup(Project $project, $branch_name)
	{
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

		return view(
			'home.setup',
			[
				'project' => $project,
				'branch_name' => $branch_name,
				'exists_setup_log' => $exists_setup_log,
				'log_checked_option' => $log_checked_option,
				'log_checked_init' => $log_checked_init,
				'log_repository' => $log_repository,
				'log_user_name' => $log_user_name,
				'log_password' => $log_password,
				'log_setup_status' => $log_setup_status,
				'log_checked_repository' => $log_checked_repository,
				'log_vendor_name' => $log_vendor_name,
				'log_project_name' => $log_project_name,
			]
		);
	}

}
