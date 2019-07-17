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
    //
    /**
     * 各アクションの前に実行させるミドルウェア
     */
    public function __construct()
    {
        // ログイン・登録完了してなくても閲覧だけはできるようにexcept()で指定します。
        $this->middleware('auth');
        $this->middleware('verified');
    }

    public function setupAjax(Request $request, Project $project, $branch_name)
    {
		//
		$checked_option = $request->checked_option;
		$project_code = $project->project_code;
		$project_path = get_project_workingtree_dir($project_code, $branch_name);
		$path_composer = realpath(__DIR__.'/../../../common/composer/composer.phar');
		$path_current_dir = realpath('.'); // 元のカレントディレクトリを記憶
		\File::makeDirectory($project_path, 0777, true, true);

		if($checked_option === 'pickles2') {
			$cmd = $path_composer.' create-project pickles2/preset-get-start-pickles2 ./';
			chdir($project_path);
		} else {
			$git_url = $request->repository;
			$git_username = $request->user_name;
			$git_password = $request->password;
			$git_url_plus_auth = $git_url;
			$cmd = '';

			if( strlen($git_username) ){
				$parsed_git_url = parse_url($git_url_plus_auth);
				$git_url_plus_auth = '';
				$git_url_plus_auth .= $parsed_git_url['scheme'].'://';
				$git_url_plus_auth .= urlencode($git_username);
				$git_url_plus_auth .= ':'.urlencode($git_password);
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
			chdir($project_path);
			shell_exec('git init');
			shell_exec('git remote set-url '.$git_url);
			if(\File::cleanDirectory($project_path)) {
				// shell_exec('rm .DS_Store');
				// clone するときは認証情報が必要なので、
				// 認証情報付きのURLで実行する
				$cmd = 'git clone --progress '.$git_url.' .';
			}
		}

		$desc = array(
		    1 => array('pipe', 'w'),
		    2 => array('pipe', 'w'),
		);
		$proc = proc_open($cmd, $desc, $pipes);
		stream_set_blocking($pipes[1], 0);
		stream_set_blocking($pipes[2], 0);
		// 標準出力が------------かどうかを判定する変数

		while (feof($pipes[1]) === false || feof($pipes[2]) === false) {
			$stdout = $stderr = '';
			$read = array($pipes[1], $pipes[2]);
			$write = null;
			$except = null;
			$timeout = 1;
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
			// ブロードキャストイベントに標準出力、標準エラー出力、パース結果を渡す、判定変数、キュー数、アラート配列、経過時間配列、パブリッシュファイルを渡す
			broadcast(new \App\Events\SetupEvent($stdout, $stderr, $process, $pipes, $path_composer, $std_parse, $std_array, $numerator, $denominator, $rate));
		}
		fclose($pipes[1]);
		fclose($pipes[2]);
		proc_close($proc);

		// .px_execute.phpの存在確認
		if(\File::exists($project_path.'/.px_execute.php')) {
			// ここから configのmaster_formatをtimestampに変更してconfig.phpに上書き保存
			if(\File::exists($project_path.'/px-files/config.php')) {
				$files = null;
				$file = file($project_path.'/px-files/config.php');
				for($i = 0; $i < count($file); $i++) {
					if(strpos($file[$i], "'master_format'=>'xlsx'") !== false) {
						$files .= str_replace('xlsx', 'timestamp', $file[$i]);
					} else {
						$files .= $file[$i];
					}
				}
				file_put_contents($project_path.'/px-files/config.php', $files);
			}

			// ここから .htaccessの一部をweb版用に修正
			if(\File::exists($project_path.'/.htaccess')) {
				$files = null;
				$file = file($project_path.'/.htaccess');
				for($i = 0; $i < count($file); $i++) {
					if(strpos($file[$i], "\.px_execute\.php/") !== false) {
						$files .= str_replace('\.px_execute\.php/', '/\.px_execute\.php/', $file[$i]);
					} else {
						$files .= $file[$i];
					}
				}
				file_put_contents($project_path.'/.htaccess', $files);
			}

			//
			if($checked_option !== 'pickles2') {
				shell_exec($path_composer.' install');
				$info = true;
			} else {
				$info = true;
			}
		} else {
			$info = false;
		}

		chdir($path_current_dir); // 元いたディレクトリへ戻る

		clearstatcache();

		$data = array(
			"info" => $info,
			"checked_option" => $checked_option
		);
		return $data;
    }

	public function setupOptionAjax(Request $request, Project $project, $branch_name)
	{
		//
		$checked_init = $request->checked_init;
		$vendor_name = $request->vendor_name;
		$project_name = $request->project_name;
		$repository = $request->repository;
		$user_name = $request->user_name;
		$password = $request->password;

		$project_code = $project->project_code;
		$project_path = get_project_workingtree_dir($project_code, $branch_name);

		$data = array(
			"checked_init" => $checked_init,
			"vendor_name" => $vendor_name,
			"project_name" => $project_name,
			"repository" => $repository,
			"user_name" => $user_name,
			"password" => $password
		);
		return $data;
	}
}
