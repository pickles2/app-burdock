<?php

/**
 * Pickles 2 を実行する
 * 
 * @param string $project_code プロジェクトコード名
 * @param string $branch_name ブランチ名
 * @param string $query Pickles 2 に渡されるパス情報。
 * PXコマンド等が実行される場合、これを含む。
 * 例: /index.html
 * 例: /hoge/fuga.html?PX=px2dthelper.get.all
 * 
 * @return string Pickles 2 が出力した文字列。
 * 期待される出力がJSONとは限らないので、デコードは呼び出し側の責任とする。
 */
function px2query($project_code, $branch_name, $query, $px2agentOptions = array()){

	// Pickles 2 の EntryScript
	$project_path = get_project_workingtree_dir($project_code, $branch_name);
	if( !is_dir($project_path) ){
		return false;
	}
	$realpath_entry_script = $project_path.'/'.get_px_execute_path($project_code, $branch_name);
	if( !\File::exists($realpath_entry_script) ){
		return false;
	}

	$init_options = array(
		'bin' => config('burdock.command_path.php'),
		'ini' => config('burdock.command_path.php_ini'),
		'extension_dir' => config('burdock.command_path.php_extension_dir'),
	);

	$px2agent = new \picklesFramework2\px2agent\px2agent($init_options);
	$px2proj = $px2agent->createProject($realpath_entry_script);

	return $px2proj->query($query, $px2agentOptions);
}
