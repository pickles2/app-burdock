<?php

function get_px_execute($project_code, $branch_name, $option)
{
	//
	$project_path = get_project_workingtree_dir($project_code, $branch_name);
	if( !is_dir($project_path) ){
		return false;
	}
	$path_current_dir = realpath('.'); // 元のカレントディレクトリを記憶

	if(!\File::exists($project_path.'/composer.json')) {
		return false;
	}
	if(!\File::exists($project_path.'/vendor')) {
		return false;
	}
	if(!\File::exists(get_px_execute_path($project_code, $branch_name))) {
		return false;
	}

	chdir($project_path);
	$bd_json = shell_exec('php '.get_px_execute_path($project_code, $branch_name).$option);
	chdir($path_current_dir); // 元いたディレクトリへ戻る
	$bd_object = json_decode($bd_json);

	return $bd_object;
}
