<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class PublishJob implements ShouldQueue
{
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	private $project_code;
	private $branch_name;
	private $publish_options;

	/**
	 * Create a new job instance.
	 *
	 * @return void
	 */
	public function __construct( $project_code, $branch_name, $publish_options = array() )
	{
		$this->project_code = $project_code;
		$this->branch_name = $branch_name;
		$this->publish_options = $publish_options;
	}

	/**
	 * Execute the job.
	 *
	 * @return void
	 */
	public function handle()
	{

		$publish_option = $this->publish_options['publish_option'];
		$paths_region = $this->publish_options['paths_region'];
		$paths_ignore = $this->publish_options['paths_ignore'];
		$keep_cache = $this->publish_options['keep_cache'];

		$project_code = $this->project_code;
		$branch_name = $this->branch_name;

		$project_path = \get_project_workingtree_dir($project_code, $branch_name);
		$path_current_dir = realpath('.'); // 元のカレントディレクトリを記憶

		set_time_limit(60);

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
			set_time_limit(60);
			$stdout = $stderr = '';
			$read = array($pipes[1], $pipes[2]);
			$write = null;
			$except = null;
			$timeout = 60000;
			$ret = stream_select($read, $write, $except, $timeout);
			if ($ret === false) {
				// echo "error\n";
				break;
			} else if ($ret === 0) {
				// echo "timeout\n";
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
			broadcast(new \App\Events\PublishEvent($project_code, $branch_name, $parse, $judge, $queue_count, $publish_file, $end_publish, $process, $pipes));
		}

		fclose($pipes[1]);
		fclose($pipes[2]);
		proc_close($proc);
		chdir($path_current_dir); // 元いたディレクトリへ戻る

		return;
	}
}
