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

	/**
	 * 保持期間設定値を解釈し、タイムスタンプを導く
	 * @return int 保持期間
	 */
	public function resolve_period_config( $conf_value ){

		if( !strlen($conf_value) ){
			// 空白ならNG
			trigger_error('The retention period setting value "'.$conf_value.'" is invalid format.');
			return false;
		}

		if( strtolower($conf_value) == 'forever' ){
			// キーワード `forever` の場合、期限を設けない。 (= false)
			return false;
		}

		$rtn = 0;
		$tmpValStr = strtolower($conf_value);

		while(1){

			if( !strlen($tmpValStr) ){
				break;
			}

			if( !preg_match('/^([0-9]+)([a-z]+)?(.*)$/', $tmpValStr, $matched) ){
				// 形式エラー
				trigger_error('The retention period setting value "'.$conf_value.'" is invalid format.');
				return false;
			}

			$int = $matched[1];
			$unit = $matched[2];
			$tmpValStr = $matched[3];

			if( $int !== '0' && strpos($int, '0') === 0 ){
				// 形式エラー: ゼロから始まる数値を含むため。
				trigger_error('The retention period setting value "'.$conf_value.'" is invalid format.');
				return false;
			}

			$int = intval($int);

			switch( $unit ){
				case '':
					// 秒
					break;
				case 'min':
					// 分
					$int = $int * 60;
					break;
				case 'h':
					// 時
					$int = $int * 60 * 60;
					break;
				case 'd':
					// 日
					$int = $int * 60 * 60 * 24;
					break;
				case 'm':
					// 月
					$int = $int * 60 * 60 * 24 * 30;
					break;
				case 'y':
					// 年
					$int = $int * 60 * 60 * 24 * 365;
					break;
				default:
					// 形式エラー: 未定義の単位
					trigger_error('The retention period setting value "'.$conf_value.'" is invalid format.');
					return false;
					break;
			}


			$rtn += $int;
			continue;
		}

		return $rtn;
	}

}
