<?php
namespace App\Helpers;

use Illuminate\Support\Facades\View;
use App\Project;

class utils{

	/**
	 * Constructor
	 */
	public function __construct(){
	}

	/**
	 * プレビューホスト名を取得する
	 */
	public static function preview_host_name( $project_code, $branch_name ){
		$subdomain = urlencode($project_code).'----'.urlencode($branch_name);
		$domain_conf = config('burdock.preview_domain');

		$rtn = '';
		if( strpos($domain_conf, '*') !== false ){
			// ワイルドカードが使用されている
			$rtn .= preg_replace( '/\*/', $subdomain, $domain_conf );
		}else{
			// ワイルドカードが使用されていない
			$rtn .= $subdomain.'.'.$domain_conf;
		}
		return $rtn;
	}

	/**
	 * ステージングホスト名を取得する
	 */
	public static function staging_host_name( $project_code, $staging_name ){
		$subdomain = urlencode($project_code).'---'.urlencode($staging_name);
		$domain_conf = config('burdock.staging_domain');

		$rtn = '';
		if( strpos($domain_conf, '*') !== false ){
			// ワイルドカードが使用されている
			$rtn .= preg_replace( '/\*/', $subdomain, $domain_conf );
		}else{
			// ワイルドカードが使用されていない
			$rtn .= $subdomain.'.'.$domain_conf;
		}
		return $rtn;
	}

	/**
	 * 現在のプロジェクトの path_controot を得る
	 */
	public static function get_path_controot( $project_code = null, $staging_name = null ){
		$fs = new \tomk79\filesystem();
		$global = View::shared('global');
		if( !is_object($global) ){
			$global = new \stdClass();
		}

		if( strlen($project_code) && strlen($staging_name) ){
			$burdockProjectManager = new \tomk79\picklesFramework2\burdock\projectManager\main( config('burdock.data_dir') );
			$project_branch = $burdockProjectManager->project($project_code)->branch($staging_name, 'preview');
			$global->px2all = $project_branch->get_project_info();
		}

		if( !property_exists( $global, 'px2all' ) ){
			return '';
		}


		$tmp_preview_path = '';
		if( property_exists( $global->px2all, 'config' ) && property_exists( $global->px2all->config, 'path_controot' ) ){
			if( strlen( $global->px2all->config->path_controot ) ){
				$tmp_preview_path = $fs->get_realpath($global->px2all->config->path_controot);
			}
		}
		return $tmp_preview_path;
	}

}
