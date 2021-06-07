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
		$global->appearance = null;
		$global->main_menu = null;
		$global->shoulder_menu = null;
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

		if( is_object($global->px2all) && property_exists($global->px2all, 'px2dtconfig') ){
			if( property_exists($global->px2all->px2dtconfig, 'appearance') && is_object($global->px2all->px2dtconfig->appearance) ){
				$global->appearance = $global->px2all->px2dtconfig->appearance;
				if( !property_exists($global->appearance, 'main_color') || !strlen($global->appearance->main_color) ){
					$global->appearance->main_color = '#000';
				}
			}

			$overwritableMenuItems = $this->get_overwritable_menu_definition($global->project_code, $global->branch_name);

			$main_menu = array(
				'*home' => true,
				'*sitemaps' => true,
				'*themes' => true,
				'*contents' => true,
				'*publish' => true,
			);


			if( property_exists($global->px2all->px2dtconfig, 'custom_console_extensions') ){
				$cceResult = px2query(
					$global->project_code,
					$global->branch_name,
					'/?PX=px2dthelper.custom_console_extensions',
					array(
						'output' => 'json',
					)
				);

				$global->cce = (object) array();
				foreach( $cceResult->list as $cce_id=>$cce_row ){
					if( !$cce_row->class_name ){
						$global->cce->{$cce_id} = false;
						continue;
					}
					if( property_exists( $overwritableMenuItems, $cce_id ) ){
						$overwritableMenuItems->{$cce_id} = (object) array(
							"id" => $cce_id,
							"label" => $cce_row->label,
							"href" => 'custom_console_extensions/'.urlencode($cce_id).'/'.urlencode($global->project_code).'/'.urlencode($global->branch_name).'/',
							"app" => 'custom_console_extensions.'.urlencode($cce_id),
						);
						continue;
					}
					$global->cce->{$cce_id} = (object) array(
						"id" => $cce_id,
						"label" => $cce_row->label,
						"href" => 'custom_console_extensions/'.urlencode($cce_id).'/'.urlencode($global->project_code).'/'.urlencode($global->branch_name).'/',
						"app" => 'custom_console_extensions.'.urlencode($cce_id),
					);
				}
				// $global->cce = $cceResult->list;
			}

			if( property_exists($global->px2all->px2dtconfig, 'main_menu') && is_array($global->px2all->px2dtconfig->main_menu) ){
				$main_menu = array();
				foreach( $global->px2all->px2dtconfig->main_menu as $main_menu_id ){
					$main_menu[$main_menu_id] = true;
				}

			}

			$global->main_menu = (object) array();
			$global->shoulder_menu = (object) array();
			foreach( $main_menu as $main_menu_id => $row ){
				if( property_exists( $overwritableMenuItems, $main_menu_id ) ){
					$global->main_menu->{$main_menu_id} = $overwritableMenuItems->{$main_menu_id};
					unset($overwritableMenuItems->{$main_menu_id});
				}elseif( property_exists( $global->cce, $main_menu_id ) ){
					$global->main_menu->{$main_menu_id} = $global->cce->{$main_menu_id};
					unset($global->cce->{$main_menu_id});
				}
			}
			$global->shoulder_menu = $overwritableMenuItems;

		}

		return $next($request);
	}


	/**
	 * 上書き可能なメニューの定義を取得
	 */
	private function get_overwritable_menu_definition($project_code, $branch_name){

		$overwritableMenuItems = (object) array(
			'*home' => (object) array(
				"id" => "*home",
				"label" => 'ホーム',
				"href" => 'home/'.urlencode($project_code).'/'.urlencode($branch_name).'/',
				"app" => "home",
			),
			'*sitemaps' => (object) array(
				"id" => "*sitemaps",
				"label" => 'サイトマップ',
				"href" => 'sitemaps/'.urlencode($project_code).'/'.urlencode($branch_name).'/',
				"app" => "sitemaps",
			),
			'*themes' => (object) array(
				"id" => "*themes",
				"label" => 'テーマ',
				"href" => 'themes/'.urlencode($project_code).'/'.urlencode($branch_name).'/',
				"app" => "themes",
			),
			'*contents' => (object) array(
				"id" => "*contents",
				"label" => 'コンテンツ',
				"href" => 'contents/'.urlencode($project_code).'/'.urlencode($branch_name).'/',
				"app" => "contents",
			),
			'*publish' => (object) array(
				"id" => "*publish",
				"label" => 'パブリッシュ',
				"href" => 'publish/'.urlencode($project_code).'/'.urlencode($branch_name).'/',
				"app" => "publish",
			),
			'*composer' => (object) array(
				"id" => "*composer",
				"label" => 'Composerを操作する',
				"href" => 'composer/'.urlencode($project_code).'/'.urlencode($branch_name).'/',
				"app" => "composer",
			),
			// '*modules' => (object) array(
			// 	"id" => "*modules",
			// 	"label" => 'モジュールを編集する',
			// 	"href" => 'modules/'.urlencode($project_code).'/'.urlencode($branch_name).'/',
			// 	"app" => "modules",
			// ),
			'*git' => (object) array(
				"id" => "*git",
				"label" => 'Gitを操作する',
				"href" => 'git/'.urlencode($project_code).'/'.urlencode($branch_name).'/',
				"app" => "git",
			),
			'*staging' => (object) array(
				"id" => "*staging",
				"label" => 'ステージング管理',
				"href" => 'staging/'.urlencode($project_code).'/'.urlencode($branch_name).'/',
				"app" => "staging",
			),
			'*delivery' => (object) array(
				"id" => "*delivery",
				"label" => '配信管理',
				"href" => 'delivery/'.urlencode($project_code).'/'.urlencode($branch_name).'/',
				"app" => "delivery",
			),
			'*clearcache' => (object) array(
				"id" => "*clearcache",
				"label" => 'キャッシュを消去する',
				"href" => 'clearcache/'.urlencode($project_code).'/'.urlencode($branch_name).'/',
				"app" => "clearcache",
			),
		);

		return $overwritableMenuItems;
	}

}
