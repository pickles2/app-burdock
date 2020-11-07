<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Project;
use App\Http\Requests\StoreSitemap;

class StagingController extends Controller
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
	public function index(Request $request, Project $project, $branch_name){

		return view(
			'staging.index',
			[
				'project' => $project,
				'branch_name' => $branch_name,
			]
		);
	}


	/**
	 * GPI
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function gpi(Request $request, Project $project, $branch_name){

		$gitUtil = new \pickles2\burdock\git($project);
		$default_branch_name = $gitUtil->get_branch_name();

		$fs = new \tomk79\filesystem();

		$realpath_pj_git_root = env('BD_DATA_DIR').'/projects/'.urlencode($project->project_code).'/plum_temporary_data_dir/';
		$fs->mkdir_r($realpath_pj_git_root);
		$fs->mkdir_r(env('BD_DATA_DIR').'/stagings/');

		$preview_server = array();
		for( $i = 1; $i <= 10; $i ++ ){
			array_push($preview_server, array(
				'name' => 'stg'.$i.'',
				'path' => env('BD_DATA_DIR').'/stagings/'.urlencode($project->project_code).'---stg'.$i.'/',
				'url' => 'http'.($_SERVER["HTTPS"] ? 's' : '').'://'.urlencode($project->project_code).'---stg'.$i.'.'.env('BD_PLUM_STAGING_DOMAIN').'/',
			));
		}

		$git_username = null;
		if( strlen($project->git_username) ){
			$git_username = \Crypt::decryptString( $project->git_username );
		}
		$git_password = null;
		if( strlen($project->git_password) ){
			$git_password = \Crypt::decryptString( $project->git_password );
		}


		$plum = new \hk\plum\main(
			array(
				'temporary_data_dir' => $realpath_pj_git_root,
				'preview_server' => $preview_server,
				'git' => array(
					'url' => $project->git_url,
					'username' => $git_username,
					'password' => $git_password,
				)
			)
		);

		$json = $plum->gpi( $_POST['data'] );

		header('Content-type: application/json');
		return json_encode( $json );
	}

}
