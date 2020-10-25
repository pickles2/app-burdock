<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Project;
use App\Http\Requests\StoreSitemap;

class SearchController extends Controller
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
			'search.index',
			[
				'project' => $project,
				'branch_name' => $branch_name,
			]
		);
	}


	/**
	 * Search API
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function api(Request $request, Project $project, $branch_name){
		$rtn = new \stdClass();
		$rtn->result = true;

		// array_push( $rtn, $gitUtil->git( $git_command_array ) );
		header('Content-type: application/json');
		return json_encode($rtn);
	}

}
