<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Project;
use App\Http\Requests\StoreSitemap;

class FilesAndFoldersController extends Controller
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
        return view('files_and_folders.index', ['project' => $project, 'branch_name' => $branch_name]);
	}


	/**
	 * Show the application dashboard.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function remoteFinderGPI(Request $request, Project $project, $branch_name){

		$remoteFinder = new tomk79\remoteFinder\main(array(
			'default' => '/path/to/root_dir/'
		), array(
			'paths_invisible' => array(
				'/invisibles/*',
				'*.hide'
			),
			'paths_readonly' => array(
				'/readonly/*',
			),
		));
		$value = $remoteFinder->gpi( json_decode( $_REQUEST['data'] ) );
		return $value;
	}

}
