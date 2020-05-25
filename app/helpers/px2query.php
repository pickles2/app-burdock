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
function px2query($project_code, $branch_name, $query){
	$php_command = array();

	// PHPコマンド
	array_push($php_command, addslashes('php'));

	// Pickles 2 の EntryScript
	$project_path = get_project_workingtree_dir($project_code, $branch_name);
	if( !is_dir($project_path) ){
		return false;
	}
	$realpath_entry_script = $project_path.'/'.get_px_execute_path($project_code, $branch_name);
	if(!\File::exists($realpath_entry_script)) {
		return false;
	}
	array_push($php_command, escapeshellarg($realpath_entry_script));

	// Request Path
	array_push($php_command, escapeshellarg($query));


	// --------------------------------------


	$str_command = implode( ' ', $php_command );

	$path_current_dir = realpath('.'); // 元のカレントディレクトリを記憶
	chdir($project_path);

	// コマンドを実行
	ob_start();
	$proc = proc_open($str_command, array(
		0 => array('pipe','r'),
		1 => array('pipe','w'),
		2 => array('pipe','w'),
	), $pipes);
	$io = array();
	foreach($pipes as $idx=>$pipe){
		// TODO: 大きな出力を受け取ろうとすると、↓このような E_NOTICE を発して落ちる。
		// 分割して受け取るようにする必要がある？
		// stream_get_contents(): read of 8192 bytes failed with errno=9 Bad file descriptor
		$io[$idx] = @stream_get_contents($pipe);
		fclose($pipe);
	}
	$return_var = proc_close($proc);
	ob_get_clean();

	$bin = $io[1]; // stdout
	if( strlen( $io[2] ) ){
		$bin .= $io[2]; // stderr
	}

	chdir($path_current_dir); // 元いたディレクトリへ戻る


	// $bin = json_decode($bin); // ← 期待される出力がJSONとは限らないので、デコードは呼び出し側の責任とする。

	return $bin;
}
