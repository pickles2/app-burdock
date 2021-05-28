<?php

/**
 * プロジェクトのブランチ別ワーキングツリーのパスを取得する
 */
function get_project_workingtree_dir($project_code, $branch_name) {
	$project_path = config('burdock.data_dir').'/repositories/'.urlencode($project_code).'----'.urlencode($branch_name).'/';
	if( file_exists($project_path) ){
		$project_path = realpath($project_path);
	}
	if( is_dir($project_path) ){
		$project_path .= ($project_path ? '/' : '');
	}
	return $project_path;
}
