<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\View;

class project
{
	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		$global = View::shared('global');

		$global->function_name = null;
		$global->cce_id = null;
		$global->project_code = null;
		$global->branch_name = null;
		$global->px2all = null;
		$global->cce = null;
		$global->project = null;
		$global->project_status = null;

		$request_path = $request->path();

		if( preg_match('/^custom_console_extensions\/([a-zA-Z0-9\_\-]+)\/([a-zA-Z0-9\_\-]+)\/([a-zA-Z0-9\_\-]+)(?:\/[\s\S]*)?$/is', $request_path, $matched) ){
			$global->function_name = 'custom_console_extensions';
			$global->cce_id = $matched[1];
			$global->project_code = $matched[2];
			$global->branch_name = $matched[3];
		}elseif( preg_match('/^([a-zA-Z0-9\_\-]+)\/([a-zA-Z0-9\_\-]+)\/([a-zA-Z0-9\_\-]+)(?:\/[\s\S]*)?$/', $request_path, $matched) ){
			$global->function_name = $matched[1];
			$global->cce_id = null;
			$global->project_code = $matched[2];
			$global->branch_name = $matched[3];
		}elseif( preg_match('/^([a-zA-Z0-9\_\-]+)\/([a-zA-Z0-9\_\-]+)(?:\/[\s\S]*)?$/', $request_path, $matched) ){
			$global->function_name = $matched[1];
			$global->cce_id = null;
			$global->project_code = $matched[2];
		}
		if( strlen($global->project_code) ){
			$global->project = \App\Project::where('project_code', $global->project_code)->first();
		}
		if( $global->project && !strlen($global->branch_name) ){
			$global->branch_name = $global->project->git_main_branch_name;
		}
		if( !strlen($global->branch_name) ){
			$global->branch_name = 'master';
		}


		if( strlen($global->project_code) && strlen($global->branch_name) ){
			$burdockProjectManager = new \tomk79\picklesFramework2\burdock\projectManager\main( config('burdock.data_dir') );
			$project_branch = $burdockProjectManager->project($global->project_code)->branch($global->branch_name, 'preview');
			$global->project_status = $project_branch->status();
			if( $global->project_status->isPxStandby ){
				$global->px2all = $project_branch->get_project_info();
			}
		}

		if( is_object($global->px2all) && property_exists($global->px2all, 'px2dtconfig') && property_exists($global->px2all->px2dtconfig, 'custom_console_extensions') ){
			$cceResult = px2query(
				$global->project_code,
				$global->branch_name,
				'/?PX=px2dthelper.custom_console_extensions',
				array(
					'output' => 'json',
				)
			);
			$global->cce = $cceResult->list;
		}

		return $next($request);
	}
}
