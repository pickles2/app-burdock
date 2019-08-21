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
		$realpath_basedir = get_project_workingtree_dir($project->project_code, $branch_name);

		$remoteFinder = new \tomk79\remoteFinder\main(array(
			'default' => realpath($realpath_basedir).'/',
		), array(
			'paths_invisible' => array(
			),
			'paths_readonly' => array(
				'/.git/*',
				'/vendor/*',
				'/node_modules/*',
			),
		));

		$value = $remoteFinder->gpi( json_decode( $request->data ) );
		return json_encode($value);
	}


	/**
	 * Common File Editor
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function commonFileEditor(Request $request, Project $project, $branch_name){
		$filename = $request->filename;

		return view(
			'files_and_folders.common_file_editor',
			[
				'project' => $project,
				'branch_name' => $branch_name,
				'filename' => $filename
			]
		);
	}

	/**
	 * Common File Editor: API: read, write
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function commonFileEditorGPI(Request $request, Project $project, $branch_name){
		$realpath_basedir = get_project_workingtree_dir($project->project_code, $branch_name);
		$rtn = array();
		$realpath_filename = $realpath_basedir.'/'.$request->filename;

		if( $request->method == 'read' ){
			$bin = \File::get( $realpath_filename );
			$rtn['base64'] = base64_encode($bin);

		}elseif( $request->method == 'write' ){
			$bin = base64_decode( $request->base64 );
			$rtn['result'] = \File::put( $realpath_filename, $bin );
		}

		return json_encode($rtn);
	}


}
