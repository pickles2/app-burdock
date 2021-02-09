<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App;
use App\Project as AppProject;

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

		$request_path = $request->path();

		if( preg_match('/^custom_console_extensions\/([a-zA-Z0-9\_\-]+)\/([a-zA-Z0-9\_\-]+)\/([a-zA-Z0-9\_\-]+)(?:\/[\s\S]*)$/is', $request_path, $matched) ){
			$global->function_name = 'custom_console_extensions';
			$global->cce_id = $matched[1];
			$global->project_code = $matched[2];
			$global->branch_name = $matched[3];
		}elseif( preg_match('/^([a-zA-Z0-9\_\-]+)\/([a-zA-Z0-9\_\-]+)\/([a-zA-Z0-9\_\-]+)(?:\/[\s\S]*)$/', $request_path, $matched) ){
			$global->function_name = $matched[1];
			$global->cce_id = null;
			$global->project_code = $matched[2];
			$global->branch_name = $matched[3];
		}

		if( strlen($global->project_code) && strlen($global->branch_name) ){
			$global->px2all = px2query(
				$global->project_code,
				$global->branch_name,
				'/?PX=px2dthelper.get.all',
				array(
					'output' => 'json',
				)
			);
		}
		if( is_object($global->px2all) && property_exists($global->px2all, 'px2dtconfig') && property_exists($global->px2all->px2dtconfig, 'custom_console_extensions') ){
			$global->cce = px2query(
				$global->project_code,
				$global->branch_name,
				'/?PX=px2dthelper.custom_console_extensions',
				array(
					'output' => 'json',
				)
			);
		}


		return $next($request);
	}
}
