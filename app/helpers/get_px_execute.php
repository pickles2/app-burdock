<?php

function get_px_execute($project_code, $branch_name, $option)
{
	//
	$project_path = get_project_workingtree_dir($project_code, $branch_name);
	$path_current_dir = realpath('.'); // 元のカレントディレクトリを記憶
	chdir($project_path);

	if(\File::exists($project_path.'/composer.json')) {
		if(\File::exists($project_path.'/vendor')) {
			if(\File::exists(get_px_execute_path($project_code, $branch_name))) {
				$bd_json = shell_exec('php '.get_px_execute_path($project_code, $branch_name).$option);
				$bd_object = json_decode($bd_json);
			} else {
				$bd_object = false;
			}
		} else {
			$bd_object = false;
		}
	} else {
		$bd_object = false;
	}
	chdir($path_current_dir); // 元いたディレクトリへ戻る

	return $bd_object;
}
