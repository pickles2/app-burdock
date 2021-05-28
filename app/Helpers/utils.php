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
		return urlencode($project_code).'----'.urlencode($branch_name).'.'.config('burdock.preview_domain');
	}

	/**
	 * ステージングホスト名を取得する
	 */
	public static function staging_host_name( $project_code, $staging_name ){
		return urlencode($project_code).'---'.urlencode($staging_name).'.'.config('burdock.staging_domain');
	}

}
