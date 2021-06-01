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

		if( !strlen($project->git_url) ){
			return view(
				'staging.index',
				[
					'error' => 'git_remot_not_set',
					'error_message' => 'Gitリモートが設定されていません。',
					'project' => $project,
					'branch_name' => $branch_name,
				]
			);
		}

		return view(
			'staging.index',
			[
				'error' => null,
				'error_message' => null,
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
		$user = Auth::user();

		if( !strlen($project->git_url) ){
			return [
				'result' => false,
				'error' => 'git_remot_not_set',
				'error_message' => 'Gitリモートが設定されていません。',
			];
		}

		$plumHelper = new \App\Helpers\plumHelper($project, $user->id);
		$plum = $plumHelper->create_plum();
		$json = $plum->gpi( $request->data );



		// --------------------------------------
		// vhosts.conf を更新する
		$bdAsync = new \App\Helpers\async();
		$bdAsync->set_channel_name( 'system-mentenance___generate_vhosts' );
		$bdAsync->artisan(
			'bd:generate_vhosts'
		);



		header('Content-type: application/json');
		return json_encode( $json );
	}

}
