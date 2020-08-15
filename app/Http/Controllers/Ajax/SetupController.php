<?php

namespace App\Http\Controllers\Ajax;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreSetup;
use App\Http\Controllers\Controller;
use App\Project;
use App\Setup;

class SetupController extends Controller
{

    /**
     * 各アクションの前に実行させるミドルウェア
     */
    public function __construct()
    {
        // ログイン・登録完了してなくても閲覧だけはできるようにexcept()で指定します。
        $this->middleware('auth');
        $this->middleware('verified');
    }

	/**
	 * プロジェクトテンプレートのセットアップオプションに対する処理
	 */
    public function setupAjax(Request $request, Project $project, $branch_name)
    {

		$burdockProjectManager = new \tomk79\picklesFramework2\burdock\projectManager\main( env('BD_DATA_DIR') );
		$pjManager = $burdockProjectManager->project($project->project_code);

		$checked_option = $request->checked_option;
		$checked_init = $request->checked_init;
		$repository = $request->clone_repository;
		$user_name = $request->clone_user_name;
		$password = $request->clone_password;
		$setup_status = $request->setup_status;
		$checked_repository = '';
		$vendor_name = '';
		$project_name = '';

		$project_code = $project->project_code;
		$project_data_path = get_project_dir($project_code);
		$project_workingtree_path = get_project_workingtree_dir($project_code, $branch_name);
		$path_composer = realpath(__DIR__.'/../../../common/composer/composer.phar');
		$path_current_dir = realpath('.'); // 元のカレントディレクトリを記憶

		// 再セットアップ時はディレクトリ内を削除してから処理に入る
		if($request->restart === 1) {
			\File::deleteDirectory($project_workingtree_path);
		}
		\File::makeDirectory($project_data_path, 0777, true, true);
		\File::makeDirectory($project_workingtree_path, 0777, true, true);

		$pjManager->save_initializing_request(array(
			'initializing_method' => ($checked_option === 'pickles2' ? 'create' : 'fork'),
				// create = 規定の雛形(pickles2/preset-get-start-pickles2) から新規プロジェクトを作成
				// fork = 新規の別のプロジェクトをベースに新規プロジェクトを作成
			'initializing_with' => array(
				'type' => ($checked_option === 'pickles2' ? 'composer-packagist' : 'git-remote'),
				'origin' => ($checked_option === 'pickles2' ? 'pickles2/preset-get-start-pickles2' : $repository),
			),
			'git_remote' => null,
			'git_user_name' => null,
			'composer_vendor_name' => null,
			'composer_project_name' => null,
		));


		// --------------------------------------
		// プロジェクトを配置する
		if($checked_option === 'pickles2') {
			// composer-packagist から
			$cmd = 'php '.$path_composer.' create-project pickles2/preset-get-start-pickles2 ./';
			chdir($project_workingtree_path);
		} else {
			// 任意の gitリポジトリから
			$git_url_plus_auth = $repository;
			$cmd = '';

			if( strlen($user_name) ){
				$parsed_git_url = parse_url($git_url_plus_auth);
				$git_url_plus_auth = '';
				$git_url_plus_auth .= $parsed_git_url['scheme'].'://';
				$git_url_plus_auth .= urlencode($user_name);
				$git_url_plus_auth .= ':'.urlencode($password);
				$git_url_plus_auth .= '@';
				$git_url_plus_auth .= $parsed_git_url['host'];
				if( array_key_exists('port', $parsed_git_url) && strlen($parsed_git_url['port']) ){
					$git_url_plus_auth .= ':'.$parsed_git_url['port'];
				}
				$git_url_plus_auth .= $parsed_git_url['path'];
				if( array_key_exists('query', $parsed_git_url) && strlen($parsed_git_url['query']) ){
					$git_url_plus_auth .= '?'.$parsed_git_url['query'];
				}
			}
			chdir($project_workingtree_path);
			if(\File::cleanDirectory($project_workingtree_path)) {
				// shell_exec('rm .DS_Store');
				// clone するときは認証情報が必要なので、
				// 認証情報付きのURLで実行する
				$cmd = 'git clone --progress '.$git_url_plus_auth.' .';
			}
		}

		$desc = array(
		    1 => array('pipe', 'w'),
		    2 => array('pipe', 'w'),
		);
		$proc = proc_open($cmd, $desc, $pipes);
		stream_set_blocking($pipes[1], 0);
		stream_set_blocking($pipes[2], 0);

		while (feof($pipes[1]) === false || feof($pipes[2]) === false) {
			$stdout = $stderr = $std_parse = $std_array = $numerator = $denominator = $rate = '';
			$read = array($pipes[1], $pipes[2]);
			$write = null;
			$except = null;
			$timeout = 60000;
		    $ret = stream_select($read, $write, $except, $timeout);

		    if ($ret === false) {
		        echo "error\n";
		        break;
		    } else if ($ret === 0) {
		        echo "timeout\n";
		        continue;
		    } else {
		        foreach ($read as $sock) {
		            if ($sock === $pipes[2]) {
						$stdout = fgets($sock);
						// 標準出力をスペース区切りで配列に代入
						$std_array = explode(' ', $stdout);
						if($std_array[0] === 'Receiving' && $std_array[1] === 'objects:') {
							for($i = 0; $i < count($std_array); $i++) {
								if(preg_match('/\(.*?\)/', $std_array[$i])) {
									$std_parse = trim($std_array[$i], '()');
									$std_parse = explode('/', $std_parse);
									// 分子変数に数値型の標準出力配列[0]を代入
									$numerator = intval($std_parse[0]);
									// 分母変数に数値型の標準出力配列[1]を代入
									$denominator = intval($std_parse[1]);
									// 分子/分母の値を小数点切り捨てかつ文字列型に変換して$parseに代入
									$rate = strval(floor($numerator/$denominator*100));
									break;
								}
							}
						}
					} else {
						$stderr = fgets($sock);
					}
				}
			}
			$process = proc_get_status($proc);
			// ブロードキャストイベントに標準出力、標準エラー出力、パース結果を渡す、判定変数、キュー数、アラート配列、経過時間配列、パブリッシュファイルを渡す
			broadcast(new \App\Events\SetupEvent($stdout, $stderr, $path_composer, $std_parse, $std_array, $numerator, $denominator, $rate, $checked_option));
		}

		fclose($pipes[1]);
		fclose($pipes[2]);
		proc_close($proc);


		// --------------------------------------
		// composer install
		if( $checked_option !== 'pickles2' ){
			// gitリポジトリからロードした場合、
			// composer のセットアップ処理が追加で必要。
			// Packagist からセットアップした場合は同時に処理されるので不要。
			shell_exec('php '.$path_composer.' install');
		}


		// --------------------------------------
		// .px_execute.php の存在確認
		if(\File::exists($project_workingtree_path.'/'.get_px_execute_path($project_code, $branch_name))) {
			$info = true;
		} else {
			$info = false;
		}

		// $initializing_request = $pjManager->get_initializing_request();
		// $pjManager->save_initializing_request($initializing_request);

		chdir($path_current_dir); // 元いたディレクトリへ戻る

		clearstatcache();

		$data = array(
			"info" => $info,
			"checked_option" => $checked_option,
		);
		return $data;
    }

	/**
	 * プロジェクトの初期化オプションに対する処理
	 */
	public function setupOptionAjax(Request $request, Project $project, $branch_name)
	{

		$burdockProjectManager = new \tomk79\picklesFramework2\burdock\projectManager\main( env('BD_DATA_DIR') );
		$pjManager = $burdockProjectManager->project($project->project_code);

		$initializing_request = $pjManager->get_initializing_request();

		$checked_option = $request->checked_option;
		$checked_init = $request->checked_init;
		$setup_status = $request->setup_status;
		$checked_repository = $request->checked_repository;
		$vendor_name = $request->vendor_name;
		$project_name = $request->project_name;

		if($checked_option === 'pickles2') {
			if($checked_init === true) {
				$repository = $request->repository;
				$user_name = $request->user_name;
				$password = $request->password;
			} else {
				$repository = '';
				$user_name = '';
				$password = '';
			}
		} elseif($checked_option === 'git') {
			if($checked_repository === 'original') {
				$repository = $request->clone_repository;
				$user_name = $request->clone_user_name;
				$password = $request->clone_password;
			} elseif($checked_repository === 'new') {
				$repository = $request->clone_new_repository;
				$user_name = $request->clone_new_user_name;
				$password = $request->clone_new_password;
			} else {
				$repository = '';
				$user_name = '';
				$password = '';
			}
		}
		$git_url_plus_auth = $repository;
		$project_code = $project->project_code;
		$project_data_path = get_project_dir($project_code);
		$project_workingtree_path = get_project_workingtree_dir($project_code, $branch_name);
		$path_current_dir = realpath('.'); // 元のカレントディレクトリを記憶

		chdir($project_workingtree_path);

		$initializing_request = $pjManager->get_initializing_request();
		if($checked_option === 'git' && $checked_repository === 'original'){
			$initializing_request->initializing_method = 'clone'; // clone = 既存リポジトリに参加
		}
		$initializing_request->git_remote = $repository;
		$initializing_request->git_user_name = $user_name;
		$initializing_request->composer_vendor_name = $vendor_name;
		$initializing_request->composer_project_name = $project_name;
		$pjManager->save_initializing_request($initializing_request);


		// --------------------------------------
		// Burdock固有の依存設定を書き換える
		if(\File::exists($project_workingtree_path.'/'.get_px_execute_path($project_code, $branch_name))) {

			// configのmaster_formatをtimestampに変更してconfig.phpに上書き保存
			// 2020-08-15 @tomk79 : 削除 :
			// 		sitemapexcel の変換設定はプロジェクトのワークフローによって選択されるべきと考えられる。
			// 		Burdockの都合で知らずに書き換えられるのは期待と合わない。
			// 		デフォルトの挙動として妥当ではないと考えられる場合は、 プロジェクトの雛形の側で予め設定されているのが望ましい。
			//
			// if(\File::exists($project_workingtree_path.'/'.get_path_homedir($project->project_code, $branch_name).'config.php')) {
			// 	$files = null;
			// 	$file = file($project_workingtree_path.'/'.get_path_homedir($project->project_code, $branch_name).'config.php');
			// 	for($i = 0; $i < count($file); $i++) {
			// 		if(strpos($file[$i], "'master_format'=>'xlsx'") !== false) {
			// 			$files .= str_replace('xlsx', 'timestamp', $file[$i]);
			// 		} else {
			// 			$files .= $file[$i];
			// 		}
			// 	}
			// 	file_put_contents($project_workingtree_path.'/'.get_path_homedir($project->project_code, $branch_name).'config.php', $files);
			// }

			// .htaccess の一部をweb版用に修正
			// 2020-08-15 @tomk79 : 削除 :
			// 		この変換は、Apache の mod_vhost_alias を使うための変換処理だが、
			// 		一般的なvhostとの互換性や、ディレクトリ構成変更の自由を損なう。
			// 		サーバー構築の環境によって、 mod_vhost_alias を使わないこともあるので、必ず置換されるのは都合がよくない。
			// 		mod_vhost_alias を使う場合に .env に変数をセットして、それに応じて置換の処理が有効になるようにする方法も考えられるが、
			// 		同じ仕様の burdock 環境でしか動かないプロジェクトになってしまい、利便性を大きく損なう。
			// 		ひとまず手動での対応をお願いすることにし、一旦削除とする。
			// if(\File::exists($project_workingtree_path.'/.htaccess')) {
			// 	$files = null;
			// 	$file = file($project_workingtree_path.'/.htaccess');
			// 	for($i = 0; $i < count($file); $i++) {
			// 		if(strpos($file[$i], "\.px_execute\.php/") !== false) {
			// 			$files .= str_replace('\.px_execute\.php/', '/\.px_execute\.php/', $file[$i]);
			// 		} else {
			// 			$files .= $file[$i];
			// 		}
			// 	}
			// 	file_put_contents($project_workingtree_path.'/.htaccess', $files);
			// }
		}


		// --------------------------------------
		// composer.json の name を変更して上書き保存
		if(\File::exists($project_workingtree_path.'/composer.json')) {
			$name_property = $vendor_name.'/'.$project_name;
			$files = null;
			$file = file($project_workingtree_path.'/composer.json');
			for($i = 0; $i < count($file); $i++) {
				if(strpos($file[$i], '"name": "pickles2/preset-get-start-pickles2"') !== false) {
					$files .= str_replace('pickles2/preset-get-start-pickles2', $name_property, $file[$i]);
				} else {
					$files .= $file[$i];
				}
			}
			file_put_contents($project_workingtree_path.'/composer.json', $files);
		}

		// --------------------------------------
		// Git初期化操作
		if( $initializing_request->initializing_method !== 'clone' ){

			if($checked_option === 'pickles2' && $checked_init === true || $checked_option === 'git' && $checked_repository === 'new') {
				// .gitフォルダがあったら削除する
				if(\File::exists($project_workingtree_path.'/.git')) {
					\File::deleteDirectory($project_workingtree_path.'/.git');
				}
				// ユーザー名とパスワードを含むGitURLを生成
				if( strlen($user_name) ){
					$parsed_git_url = parse_url($git_url_plus_auth);
					$git_url_plus_auth = '';
					$git_url_plus_auth .= $parsed_git_url['scheme'].'://';
					$git_url_plus_auth .= urlencode($user_name);
					$git_url_plus_auth .= ':'.urlencode($password);
					$git_url_plus_auth .= '@';
					$git_url_plus_auth .= $parsed_git_url['host'];
					if( array_key_exists('port', $parsed_git_url) && strlen($parsed_git_url['port']) ){
						$git_url_plus_auth .= ':'.$parsed_git_url['port'];
					}
					$git_url_plus_auth .= $parsed_git_url['path'];
					if( array_key_exists('query', $parsed_git_url) && strlen($parsed_git_url['query']) ){
						$git_url_plus_auth .= '?'.$parsed_git_url['query'];
					}
				}

				shell_exec('git init');
				shell_exec('git add .');
				shell_exec('git commit -m "Create project"');
				shell_exec('git remote add origin '.escapeshellarg($git_url_plus_auth));

			} elseif($checked_option === 'git' && $checked_repository === 'original') {
				// ユーザー名とパスワードを含むGitURLを生成
				$git_url_plus_auth = $repository;
				if( strlen($user_name) ){
					$parsed_git_url = parse_url($git_url_plus_auth);
					$git_url_plus_auth = '';
					$git_url_plus_auth .= $parsed_git_url['scheme'].'://';
					$git_url_plus_auth .= urlencode($user_name);
					$git_url_plus_auth .= ':'.urlencode($password);
					$git_url_plus_auth .= '@';
					$git_url_plus_auth .= $parsed_git_url['host'];
					if( array_key_exists('port', $parsed_git_url) && strlen($parsed_git_url['port']) ){
						$git_url_plus_auth .= ':'.$parsed_git_url['port'];
					}
					$git_url_plus_auth .= $parsed_git_url['path'];
					if( array_key_exists('query', $parsed_git_url) && strlen($parsed_git_url['query']) ){
						$git_url_plus_auth .= '?'.$parsed_git_url['query'];
					}
				}

				shell_exec('git add .');
				shell_exec('git commit -m "After clone first commit"');
				shell_exec('git remote set-url origin '.escapeshellarg($git_url_plus_auth));
			} elseif($checked_option === 'git' && $checked_repository === 'none') {
				// .gitフォルダがあったら削除する
				if(\File::exists($project_workingtree_path.'/.git')) {
					\File::deleteDirectory($project_workingtree_path.'/.git');
				}
			}
			// git push時のブロードキャスト
			if($checked_option === 'pickles2' && $checked_init === true || $checked_option === 'git' && $checked_repository === 'original' || $checked_option === 'git' && $checked_repository === 'new') {
				$cmd = 'git push --progress origin master';
				$desc = array(
					1 => array('pipe', 'w'),
					2 => array('pipe', 'w'),
				);
				$proc = proc_open($cmd, $desc, $pipes);
				stream_set_blocking($pipes[1], 0);
				stream_set_blocking($pipes[2], 0);

				while (feof($pipes[1]) === false || feof($pipes[2]) === false) {
					$stdout = $stderr = '';
					$read = array($pipes[1], $pipes[2]);
					$write = null;
					$except = null;
					$timeout = 60000;
					$ret = stream_select($read, $write, $except, $timeout);
					if ($ret === false) {
						echo "error\n";
						break;
					} else if ($ret === 0) {
						echo "timeout\n";
						continue;
					} else {
						foreach ($read as $sock) {
							if ($sock === $pipes[1]) {
								$stdout = fgets($sock);
							} else if ($sock === $pipes[2]) {
								$stderr = fgets($sock);
								// 標準出力をスペース区切りで配列に代入
								$std_array = explode(' ', $stderr);
								if($std_array[0] === 'Writing' && $std_array[1] === 'objects:') {
									for($i = 0; $i < count($std_array); $i++) {
										if(preg_match('/\(.*?\)/', $std_array[$i])) {
											$std_parse = trim($std_array[$i], '()');
											$std_parse = explode('/', $std_parse);
											// 分子変数に数値型の標準出力配列[0]を代入
											$numerator = intval($std_parse[0]);
											// 分母変数に数値型の標準出力配列[1]を代入
											$denominator = intval($std_parse[1]);
											// 分子/分母の値を小数点切り捨てかつ文字列型に変換して$rateに代入
											$rate = strval(floor($numerator/$denominator*100));
											break;
										} else {
											$std_parse = '';
											$numerator = '';
											$denominator = '';
											$rate = '';
										}
									}
								} else {
									$std_parse = '';
									$numerator = '';
									$denominator = '';
									$rate = '';
								}
							}
						}
					}
					$process = proc_get_status($proc);
					// ブロードキャストイベントに標準出力、標準エラー出力、パース結果、アラート配列、分子変数、分母変数、レート変数を渡す
					broadcast(new \App\Events\SetupOptionEvent($stdout, $stderr, $std_parse, $std_array, $numerator, $denominator, $rate, $checked_option));
				}
				fclose($pipes[1]);
				fclose($pipes[2]);
				proc_close($proc);
			}
		}
		$info = true;

		// $initializing_request = $pjManager->get_initializing_request();
		// $pjManager->save_initializing_request($initializing_request);


		// --------------------------------------
		// Gitリモート接続情報をDBに保存
		$project->git_url = $repository;
		$project->git_username = \Crypt::encryptString($user_name);
		$project->git_password = \Crypt::encryptString($password);
		$project->save();


		chdir($path_current_dir); // 元いたディレクトリへ戻る

		$data = array(
			"info" => $info,
			"checked_option" => $checked_option,
			"checked_init" => $checked_init,
			"checked_repository" => $checked_repository,
			"vendor_name" => $vendor_name,
			"project_name" => $project_name,
			"repository" => $repository,
			"user_name" => $user_name,
			"password" => $password,
			"git_url_plus_auth" => $git_url_plus_auth,
		);
		return $data;
	}
}
