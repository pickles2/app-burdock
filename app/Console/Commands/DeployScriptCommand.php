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
		foreach($json->tasks as $task){
			if( $task->type == 'copy' ){
				var_dump( realpath($task->from) );

			}elseif( $task->type == 'remove' ){
				var_dump( $this->fs->get_realpath($task->path) );

			}elseif( $task->type == 'php-script' ){

			}elseif( $task->type == 'php-function' ){

			}
		}

		var_dump($json);

		chdir( $realpath_current_dir );
		return true;
	}

}
