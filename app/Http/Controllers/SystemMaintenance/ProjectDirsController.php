<?php

namespace App\Http\Controllers\SystemMaintenance;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Project;

class ProjectDirsController extends \App\Http\Controllers\Controller
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
	 * Show project list.
	 */
	public function index()
	{
		$burdockProjectManager = new \tomk79\picklesFramework2\burdock\projectManager\main( env('BD_DATA_DIR') );
		$projects = $burdockProjectManager->get_project_list();
		$project_dirs = array();
		foreach( $projects as $project_code ){
			$row = array();
			$row['project_code'] = $project_code;
			$row['exists_on_db'] = false;

			$project = Project::where('project_code', $project_code);
			if( $project->count() ){
				$row['exists_on_db'] = true;
			}
			array_push($project_dirs, $row);
		}

		return view(
			'system-maintenance.project-dirs.index',
			array(
				'projectDirs' => $project_dirs,
			)
		);
	}

	/**
	 * Show project details.
	 */
	public function show($project_code)
	{
		$burdockProjectManager = new \tomk79\picklesFramework2\burdock\projectManager\main( env('BD_DATA_DIR') );
		// $pj = $burdockProjectManager->project($project_code);
		// $project_dirs = array();
		// foreach( $projects as $project_code ){
		// 	$row = array();
		// 	$row['project_code'] = $project_code;
		// 	$row['exists_on_db'] = false;

		// 	if( $project->count() ){
		// 		$row['exists_on_db'] = true;
		// 	}
		// 	array_push($project_dirs, $row);
		// }
		$project = Project::where('project_code', $project_code)->first();
		// if( !$project->count() ){
		// 	$project = null;
		// }

		return view(
			'system-maintenance.project-dirs.show',
			array(
				'project_code' => $project_code,
				'project_obj' => $project,
			)
		);
	}

}
