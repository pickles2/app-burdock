<?php

namespace App\Http\Controllers\Ajax;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StorePublish;
use App\Http\Controllers\Controller;
use App\Project;
use Carbon\Carbon;

class PublishController extends Controller
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

	public function readCsvAjax(Request $request, Project $project, $branch_name)
	{
		//
		$fs = new \tomk79\filesystem;
		$project_name = $project->project_code;
		$project_path = get_project_workingtree_dir($project_name, $branch_name);

		$bd_object = px2query(
			$project->project_code,
			$branch_name,
			'/?PX=px2dthelper.get.all'
		);
		$bd_object = json_decode($bd_object);

		$publish_log_file = $bd_object->realpath_homedir.'_sys/ram/publish/publish_log.csv';
		$alert_log_file = $bd_object->realpath_homedir.'_sys/ram/publish/alert_log.csv';
		$applock_file = $bd_object->realpath_homedir.'_sys/ram/publish/applock.txt';

		if(\File::exists($publish_log_file)) {
			$exists_publish_log = true;
			$publish_log = $fs->read_csv($publish_log_file);
			$publish_files = count($publish_log) - 1;
		} else {
			$exists_publish_log = false;
			$publish_log = false;
			$publish_files = 0;
		}

		if(\File::exists($alert_log_file)) {
			$exists_alert_log = true;
			$alert_log = $fs->read_csv($alert_log_file);
			$alert_files = count($alert_log) - 1;
		} else {
			$exists_alert_log = false;
			$alert_log = false;
			$alert_files = 0;
		}

		if(\File::exists($applock_file)) {
			$exists_applock = true;
		} else {
			$exists_applock = false;
		}

		if($publish_log) {
			$dt1 = new Carbon($publish_log[array_key_last($publish_log)][0]);
			$dt2 = new Carbon($publish_log[1][0]);
			$diff_seconds = $dt1->diffInSeconds($dt2);
		} else {
			$diff_seconds = 0;
		}

		$data = array(
			"publish_log" => $publish_log,
			"alert_log" => $alert_log,
			"publish_files" => $publish_files,
			"alert_files" => $alert_files,
			'diff_seconds' => $diff_seconds,
			'exists_publish_log' => $exists_publish_log,
			'exists_alert_log' => $exists_alert_log,
			'exists_applock' => $exists_applock
        );
        return $data;
	}

    public function publishAjax(Request $request, Project $project, $branch_name)
    {
		//
		$publish_option = $request->publish_option;
		$paths_region = $request->paths_region;
		$paths_ignore = $request->paths_ignore;
		$keep_cache = $request->keep_cache;

		$project_code = $project->project_code;
		$project_path = get_project_workingtree_dir($project_code, $branch_name);
		$path_current_dir = realpath('.'); // 元のカレントディレクトリを記憶

		chdir($project_path);
		// proc_openでパブリッシュ
		$desc = array(
		    1 => array('pipe', 'w'),
		    2 => array('pipe', 'w'),
		);
		$cmd = '';
		$cmd .= 'php '.get_px_execute_path($project_code, $branch_name).' ';
		$cmd .= '"';
		$cmd .= '/?PX=publish.run';
		if(is_array($paths_region)) {
			$cmd .= '&path_region='.$paths_region[0];
			if(count($paths_region) > 1) {
				for($i = 1; $i < count($paths_region); $i++) {
					$cmd .= '&paths_region[]='.$paths_region[$i];
				}
			}
		}
		if(is_array($paths_ignore)) {
			foreach($paths_ignore as $ignore) {
				$cmd .= '&paths_ignore[]='.$ignore;
			}
		}
		if($keep_cache !== false) {
			$cmd .= '&keep_cache='.$keep_cache;
		}
		$cmd .= '"';

		$proc = proc_open($cmd, $desc, $pipes);
		stream_set_blocking($pipes[1], 0);
		stream_set_blocking($pipes[2], 0);
		// 標準出力が------------かどうかを判定する変数
		$reserve = 0;
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
						// 分子変数
						$numerator = 0;
						// 分母変数
						$denominator = 0;
						// 標準出力をスラッシュ区切りで配列に代入
						$std_array = explode('/', $stdout);
						// 判定変数
						$judge = 0;
						// 標準出力がYou gotを含む場合、配列に代入
						if(preg_match('/You got/', $stdout)) {
							$alert_array = explode(' ', $stdout);
						} else {
							$alert_array = '';
						}
						// 標準出力がTotal Timeを含む場合、配列に代入
						if(preg_match('/Total Time:/', $stdout)) {
							$time_array = explode(' ', $stdout);
						} else {
							$time_array = '';
						}
						// $reserveが1だった場合、$publish_fileに標準出力を代入
						if($reserve === 1) {
							$publish_file = $stdout;
						} else {
							$publish_file = '';
						}
						// 標準出力が------------を含む場合、$reserveに1を代入
						if(preg_match('/\-{12}/', $stdout)) {
							$reserve = 1;
						} else {
							$reserve = 0;
						}
						// 配列数が2だった場合に実行
						if (count($std_array) === 2) {
							// 標準出力配列をまわして値を取り出す
							foreach($std_array as $std) {
								// $stdが数値または数値+改行コードの場合にのみ$judgeに1が入る
								$judge = preg_match('/^[0-9]+\r?\n?$/m', $std);
							}
							if($judge === 1) {
								// 分子変数に数値型の標準出力配列[0]を代入
								$numerator = intval($std_array[0]);
								// 分母変数に数値型の標準出力配列[1]を代入
								$denominator = intval($std_array[1]);
								// 分子/分母の値を小数点切り捨てかつ文字列型に変換して$parseに代入
								$parse = strval(floor($numerator/$denominator*100));
								// キュー数に標準出力を代入
								$queue_count = $stdout;
							} else {
								$parse = '';
								$queue_count = '';
							}
						} else {
							$parse = '';
							$queue_count = '';
						}
					} else if ($sock === $pipes[2]) {
						$stderr = fgets($sock);
						$alert_array = '';
						$time_array = '';
						$publish_file = '';
					}
					if(preg_match('/PX Command END/', $stdout)) {
						$end_publish = 1;
					} else {
						$end_publish = 0;
					}
				}
			}
			$process = proc_get_status($proc);
			// ブロードキャストイベントに標準出力、標準エラー出力、パース結果を渡す、判定変数、キュー数、アラート配列、経過時間配列、パブリッシュファイルを渡す
			broadcast(new \App\Events\PublishEvent($parse, $judge, $queue_count, $publish_file, $end_publish, $process, $pipes));
		}
		fclose($pipes[1]);
		fclose($pipes[2]);
		proc_close($proc);
		chdir($path_current_dir); // 元いたディレクトリへ戻る

		$info = 'パブリッシュが完了しました。';

        $data = array(
			"info" => $info,
			"publish_option" => $publish_option,
			"paths_region" => $paths_region,
			"paths_ignore" => $paths_ignore,
			"keep_cache" => $keep_cache,
			"cmd" => $cmd
        );
        return $data;
    }

	public function publishCancelAjax(Request $request, Project $project, $branch_name)
    {
		//
		$project_code = $project->project_code;
		$project_path = get_project_workingtree_dir($project_code, $branch_name);
		$path_current_dir = realpath('.'); // 元のカレントディレクトリを記憶

		chdir($project_path);
		// パブリッシュのプロセスを強制終了
		$kill_info = exec('kill -USR1 '.$request->process);
		chdir($path_current_dir); // 元いたディレクトリへ戻る

		$bd_object = px2query(
			$project->project_code,
			$branch_name,
			'/?PX=px2dthelper.get.all'
		);
		$bd_object = json_decode($bd_object);

		// applock.txtを削除
		$applock_file = $bd_object->realpath_homedir.'_sys/ram/publish/applock.txt';
		\File::delete($applock_file);

		// 削除の結果をテキストで返す
		if(\File::exists($applock_file)) {
			$message = 'ロックファイルを削除できませんでした。';
		} else {
			$message = 'ロックファイルを削除しました。';
		}
        $data = array(
			"message" => $message,
        );
        return $data;
	}

	public function publishSingleAjax(Request $request, Project $project, $branch_name)
	{
		$path_region = $request->path_region;
		$bd_object = px2query(
			$project->project_code,
			$branch_name,
			'/?PX=publish.run&path_region='.urlencode($path_region)
		);
		$bd_object = json_decode($bd_object);

		if($bd_object !== false) {
			$info = 'をパブリッシュしました。';
		} else {
			$info = 'をパブリッシュできませんでした。';
		}

		$data = array(
			"info" => $info
		);
		return $data;
	}
}
