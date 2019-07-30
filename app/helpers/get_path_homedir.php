<?php

function get_path_homedir($project_code, $branch_name)
{
	//
	$project_path = get_project_workingtree_dir($project_code, $branch_name);
	$path_current_dir = realpath('.'); // 元のカレントディレクトリを記憶
	chdir($project_path);

	if(\File::exists($project_path.'/composer.json')) {
		$json = file_get_contents($project_path.'/composer.json');
		$json = mb_convert_encoding($json, 'UTF8', 'ASCII,JIS,UTF-8,EUC-JP,SJIS-WIN');
		$arr = json_decode($json);
		if($arr->extra->px2package->type === 'project') {
			if(\File::exists($arr->extra->px2package->path)) {
				$path_homedir = $arr->extra->px2package->path_homedir;
			} else {
				$path_homedir = false;
			}
		} else {
			$path_homedir = false;
		}
	} else {
		$path_homedir = false;
	}
	chdir($path_current_dir); // 元いたディレクトリへ戻る

	return $path_homedir;
}
