<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Project;
use App\Events\AsyncGeneralProgressEvent;

class AsyncCmdCommand extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'bd:cmd {path_json}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'コマンドを非同期で実行する。';

	/** $fs */
	private $fs;

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{

		$this->info('================================================================');
		$this->info('  Start '.$this->signature);
		$this->info('    - Local Time: '.date('Y-m-d H:i:s'));
		$this->info('    - GMT: '.gmdate('Y-m-d H:i:s'));
		$this->info('----------------------------------------------------------------');
		$this->line( '' );

		$path_json = $this->argument('path_json');
		$json = null;
		if( is_file($path_json) ){
			$json = json_decode( file_get_contents($path_json) );
		}
		if( !$json ){
			$this->line(' Nothing to do.');
			$this->line( '' );
			$this->line('Local Time: '.date('Y-m-d H:i:s'));
			$this->line('GMT: '.gmdate('Y-m-d H:i:s'));
			$this->comment('------------ '.$this->signature.' successful ------------');
			$this->line( '' );

			return 0; // 終了コード
		}

		$user_id = $json->user_id;
		$project_code = $json->project_code;
		$branch_name = $json->branch_name;
		$channel_name = $json->channel_name;

		$ary_command = $json->ary_command;
		$options = $json->options;

		$project_path = realpath('.');
		if( strlen($project_code) && strlen($branch_name) ){
			$project_path = \get_project_workingtree_dir($project_code, $branch_name);
		}
		$path_current_dir = realpath('.'); // 元のカレントディレクトリを記憶

		set_time_limit(60);

		chdir($project_path);

		// proc_open
		$desc = array(
			1 => array('pipe', 'w'),
			2 => array('pipe', 'w'),
		);


		foreach( $ary_command as $idx=>$row ){
			$ary_command[$idx] = escapeshellarg($row);
		}

		$cmd = implode(' ', $ary_command);

		$proc = proc_open($cmd, $desc, $pipes);
		stream_set_blocking($pipes[1], 0);
		stream_set_blocking($pipes[2], 0);

		while (feof($pipes[1]) === false || feof($pipes[2]) === false) {
			set_time_limit(60);
			$stdout = fgets($pipes[1]);
			$stderr = fgets($pipes[2]);

			broadcast(
				new AsyncGeneralProgressEvent(
					$user_id,
					$project_code,
					$branch_name,
					'progress',
					null,
					($stdout!==false ? $stdout : ''),
					($stderr!==false ? $stderr : ''),
					$channel_name
				)
			);
		}


		$stat = array();
		do {
			$stat = proc_get_status($proc);
			// waiting
			usleep(1);
		} while( $stat['running'] );



		broadcast(
			new AsyncGeneralProgressEvent(
				$user_id,
				$project_code,
				$branch_name,
				'exit',
				$stat['exitcode'],
				null,
				null,
				$channel_name
			)
		);




		fclose($pipes[1]);
		fclose($pipes[2]);
		proc_close($proc);
		chdir($path_current_dir); // 元いたディレクトリへ戻る




		$this->line(' finished!');
		$this->line( '' );
		$this->line('Local Time: '.date('Y-m-d H:i:s'));
		$this->line('GMT: '.gmdate('Y-m-d H:i:s'));
		$this->comment('------------ '.$this->signature.' successful ------------');
		$this->line( '' );

		return 0; // 終了コード
	}

}
