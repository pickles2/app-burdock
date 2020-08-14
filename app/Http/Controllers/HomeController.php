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
			$bd_object = $project_branch->get_project_info();

			return view('home.index', [
				'project' => $project,
				'branch_name' => $branch_name,
				'project_status' => $project_status,
			], compact('bd_object'));

		} else {
			return $this->setup($project, $branch_name);
		}
	}

	/**
	 * プロジェクトの初期セットアップを実行する
	 */
	private function setup(Project $project, $branch_name)
	{
		$burdockProjectManager = new \tomk79\picklesFramework2\burdock\projectManager\main( env('BD_DATA_DIR') );
		$pjManager = $burdockProjectManager->project($project->project_code);
		$initializing_request = $pjManager->get_initializing_request();
		if( !$initializing_request ){
			$initializing_request = new \stdClass();
		}

		return view(
			'home.setup',
			[
				'project' => $project,
				'branch_name' => $branch_name,
				'initializing_request' => $initializing_request,
			]
		);
	}

}
