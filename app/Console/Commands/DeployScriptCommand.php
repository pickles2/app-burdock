<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Project;

class DeployScriptCommand extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'bd:deploy-script';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = '本番配信ツール indigo の、配信予約処理後に実行する処理。';

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

		$this->fs = new \tomk79\filesystem();

		$result = $this->run_deploy_tasks();

		$this->line( '' );
		$this->line( '' );
		if( $result ){
			$this->line(' finished!');
		}else{
			$this->line(' ERROR!');
		}

		$this->line( '' );
		$this->line('Local Time: '.date('Y-m-d H:i:s'));
		$this->line('GMT: '.gmdate('Y-m-d H:i:s'));
		$this->comment('------------ '.$this->signature.' successful ------------');
		$this->line( '' );

		return 0; // 終了コード
	}


	/**
	 * デイプロイタスクを実行する
	 */
	private function run_deploy_tasks(){
		$realpath_current_dir = realpath('.');
		$realpath_json = __DIR__.'/../../../settings/deploy-scripts/deploy-script-config.json';
		if( !is_file( $realpath_json ) ){
			return true;
		}
		$json = json_decode( file_get_contents( $realpath_json ) );
		// var_dump($json);

		if( !is_object($json) ){
			return false;
		}
		if( !property_exists($json, 'tasks') ){
			return true;
		}
		if( !is_array($json->tasks) ){
			return false;
		}

		chdir( dirname($realpath_json) );
		foreach($json->tasks as $idx=>$task){

			$this->line('');
			$this->comment('** Task No.'.$idx.': '.$task->type);

			if( $task->type == 'copy' ){
				$realpath_from = $this->fs->get_realpath($task->from);
				$realpath_to   = $this->fs->get_realpath($task->to);
				$this->line( '   From: '.$realpath_from );
				$this->line( '   To:   '.$realpath_to );

				if( !file_exists($realpath_from) ){
					$this->error( '   Copy from is NOT exists!' );
					continue;
				}

				$this->fs->copy_r(
					$realpath_from,
					$realpath_to
				);

			}elseif( $task->type == 'remove' ){
				$realpath_target = $this->fs->get_realpath($task->path);
				$this->line( '   Remove: '.$realpath_target );
				if( !file_exists($realpath_target) ){
					$this->error( '   Target is NOT exists!' );
					continue;
				}

				$this->fs->rm( $realpath_target );

			}elseif( $task->type == 'empty-dir' ){
				$realpath_target = $this->fs->get_realpath($task->path);
				$this->line( '   Directory: '.$realpath_target );
				if( !file_exists($realpath_target) ){
					$this->error( '   Target is NOT exists!' );
					continue;
				}
				if( !is_dir($realpath_target) ){
					$this->error( '   Target is NOT a directory!' );
					continue;
				}

				$list = $this->fs->ls( $realpath_target );
				foreach( $list as $basename ){
					$this->fs->rm( $realpath_target.'/'.$basename );
				}

			}elseif( $task->type == 'php-script' ){
				$realpath_php_script = $this->fs->get_realpath($task->script);
				$this->line( '   Script: '.$realpath_php_script );
				if( !is_file($realpath_php_script) ){
					$this->error( '   Script file is NOT exists!' );
					continue;
				}
				$this->line( '' );
				ob_start();
				$proc = proc_open('php '.escapeshellarg($realpath_php_script), array(
					0 => array('pipe','r'),
					1 => array('pipe','w'),
					2 => array('pipe','w'),
				), $pipes);
				$io = array();
				foreach($pipes as $idx=>$pipe){
					$io[$idx] = stream_get_contents($pipe);
					fclose($pipe);
				}
				$return_var = proc_close($proc);
				ob_get_clean();

				$bin = $io[1]; // stdout
				if( strlen( $io[2] ) ){
					$this->error($io[2]); // stderr
				}
				$this->line( $bin );

			}elseif( $task->type == 'php-function' ){
				$this->line( '   Function: '.$task->function );
				if( !is_callable($task->function) ){
					$this->error( '   NOT Callable!' );
					continue;
				}
				call_user_func($task->function);
			}
		}

		chdir( $realpath_current_dir );
		return true;
	}

}
