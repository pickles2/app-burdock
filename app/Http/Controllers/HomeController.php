<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\User;
use App\Project;
use App\Http\Requests\StoreUser;

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
	public function index(Project $project, $branch_name = null)
	{

		if( !strlen($branch_name) ){
			$branch_name = $project->git_main_branch_name;
		}
		if( !strlen($branch_name) ){
			$branch_name = 'master';
		}

		$burdockProjectManager = new \tomk79\picklesFramework2\burdock\projectManager\main( config('burdock.data_dir') );
		$project_branch = $burdockProjectManager->project($project->project_code)->branch($branch_name, 'preview');

		$global = View::shared('global');
		$project_status = $global->project_status;

		if( $project_status->pathExists && $project_status->isPxStandby ){
			// --------------------------------------
			// セットアップは正常に完了しているとき
			$project_branch_info = $project_branch->get_project_info();

			return view('home.index', [
				'project' => $project,
				'branch_name' => $branch_name,
				'project_status' => $project_status,
				'project_branch_info' => $project_branch_info,
			]);

		}elseif( $project_status->pathExists && $project_status->composerJsonExists && !$project_status->vendorDirExists ){
			// --------------------------------------
			// composer.json はすでにあるが、vendorディレクトリはまだないとき

			return view('home.do_composer', [
				'project' => $project,
				'branch_name' => $branch_name,
				'project_status' => $project_status,
			]);

		}

		// --------------------------------------
		// セットアップされていないとき
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


	/**
	 * プロジェクトテンプレートのセットアップオプションに対する処理
	 */
    public function setupAjax(Request $request, Project $project, $branch_name)
    {
		$params = array();

		$params['checked_option'] = $request->checked_option;
		$params['checked_init'] = $request->checked_init;
		$params['clone_repository'] = $request->clone_repository;
		$params['clone_user_name'] = $request->clone_user_name;
		$params['clone_password'] = $request->clone_password;
		$params['setup_status'] = $request->setup_status;
		$params['restart'] = $request->restart;

		$bdAsync = new \App\Helpers\async($project, $branch_name);
		$bdAsync->set_channel_name( 'setup-event' );
		$bdAsync->artisan(
			'bd:setup',
			array(),
			$params
		);

		return array(
			'result' => true,
			'message' => 'OK',
		);
    }

	/**
	 * プロジェクトの初期化オプションに対する処理
	 */
	public function setupOptionAjax(Request $request, Project $project, $branch_name)
	{
		$params = array();

		$params['checked_option'] = $request->checked_option;
		$params['checked_init'] = $request->checked_init;
		$params['setup_status'] = $request->setup_status;
		$params['checked_repository'] = $request->checked_repository;
		$params['vendor_name'] = $request->vendor_name;
		$params['project_name'] = $request->project_name;

		$params['repository'] = $request->repository;
		$params['user_name'] = $request->user_name;
		$params['password'] = $request->password;

		$params['clone_repository'] = $request->clone_repository;
		$params['clone_user_name'] = $request->clone_user_name;
		$params['clone_password'] = $request->clone_password;

		$params['clone_new_repository'] = $request->clone_new_repository;
		$params['clone_new_user_name'] = $request->clone_new_user_name;
		$params['clone_new_password'] = $request->clone_new_password;



		$bdAsync = new \App\Helpers\async($project, $branch_name);
		$bdAsync->set_channel_name( 'setup-option-event' );
		$bdAsync->artisan(
			'bd:setup_options',
			array(),
			$params
		);

		return array(
			'result' => true,
			'message' => 'OK',
		);
	}

}
