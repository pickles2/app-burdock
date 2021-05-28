<?php
namespace App\Helpers;

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

}
