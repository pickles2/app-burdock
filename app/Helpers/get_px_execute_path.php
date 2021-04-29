<?php

function get_px_execute_path($project_code, $branch_name)
{
	$project_path = get_project_workingtree_dir($project_code, $branch_name);
	$px_execute_path = '.px_execute.php'; // <- default

	if( !is_dir($project_path) ){
		return false;
	}
	if(!\File::exists($project_path.'/composer.json')) {
		return false;
	}

	$json = file_get_contents($project_path.'/composer.json');
	$json = mb_convert_encoding($json, 'UTF8', 'ASCII,JIS,UTF-8,EUC-JP,SJIS-WIN');
	$arr = json_decode($json);

	$packageInfos = array();
	if( is_object($arr) && property_exists($arr, 'extra') && property_exists($arr->extra, 'px2package') ){
		if( is_array($arr->extra->px2package) ){
			$packageInfos = $arr->extra->px2package;
		}elseif( is_object($arr->extra->px2package) ){
			array_push($packageInfos, $arr->extra->px2package);
		}
	}
	foreach( $packageInfos as $packageInfo ){
		if( $packageInfo->type === 'project' ){
			if(\File::exists($project_path.'/'.$arr->extra->px2package->path)) {
				$px_execute_path = $arr->extra->px2package->path;
			}
			break;
		}
	}

	// 相対パスで書かれなければいけない
	$px_execute_path = preg_replace('/^[\\/\\\\\\:\\;]*/si', '', $px_execute_path);

	return $px_execute_path;
}
