<?php

/**
 * プロジェクトのデータディレクトリのパスを取得する
 */
function get_project_dir($project_code) {
	$project_path = config('burdock.data_dir').'/projects/'.urlencode($project_code).'/';
	if( file_exists($project_path) ){
		$project_path = realpath($project_path);
	}
	if( is_dir($project_path) ){
		$project_path .= ($project_path ? '/' : '');
	}
	return $project_path;
}
