<?php

namespace App\Http\Controllers\Ajax;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StorePublish;
use App\Http\Controllers\Controller;
use App\Project;

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

    public function publishAjax(Request $request, Project $project, $branch_name)
    {
		//
		$project_code = $project->project_code;
		$project_path = get_project_workingtree_dir($project_code, $branch_name);
		$path_current_dir = realpath('.'); // 元のカレントディレクトリを記憶

		chdir($project_path);
		// proc_openでパブリッシュしてみる
		$desc = array(
		    1 => array('pipe', 'w'),
		    2 => array('pipe', 'w'),
		);
		$proc = proc_open('php .px_execute.php /?PX=publish.run', $desc, $pipes);
		stream_set_blocking($pipes[1], 0);
		stream_set_blocking($pipes[2], 0);
		// 標準出力が------------かどうかを判定する変数
		$reserve = 0;
		$total_files = -1;
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
								$total_files++;
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
			// ブロードキャストイベントに標準出力、標準エラー出力、パース結果を渡す、判定変数、キュー数、アラート配列、経過時間配列、パブリッシュファイルを渡す
			broadcast(new \App\Events\PublishEvent($stdout, $stderr, $parse, $judge, $queue_count, $alert_array, $time_array, $publish_file, $end_publish, $total_files));
		}
		fclose($pipes[1]);
		fclose($pipes[2]);
		proc_close($proc);
		chdir($path_current_dir); // 元いたディレクトリへ戻る

		$info = 'パブリッシュが完了しました。';

        $data = array(
			"info" => $info,
        );
        return $data;
    }
}
