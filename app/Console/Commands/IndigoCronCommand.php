<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\DeliveryController;
use App\Project;

class IndigoCronCommand extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'indigo:cron';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = '本番配信ツール indigo が、配信予約に従って配信を実行する。';

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

		$indigoController = new DeliveryController();

		$projects = Project::all();
		if( !$projects ){
			$this->error('Failed to load Project list.');
			return 1;
		}

		$this->fs = new \tomk79\filesystem();

		$count = count($projects);
		$current = 0;

		foreach ($projects as $project) {
			$current ++;
			$this->line( '' );
			$this->info( '[ '.$current.'/'.$count.' ] '.$project->project_name );
			$this->line( '   - '.$project->id );
			$this->line( '   - '.$project->project_code );

			if( !strlen($project->git_url) ){
				$this->line( '`git_url` is not set.' );
				$this->line( '                     -----> Skip' );
				$this->line( '' );
				continue;
			}

			$gitUtil = new \App\Helpers\git($project);
			$default_branch_name = $gitUtil->get_branch_name();

			$parameter = $indigoController->mk_indigo_options($project, $default_branch_name);

			// load indigo\main
			$indigo = new \indigo\main($parameter);

			// 実行する
			$result = $indigo->cron_run();
			$this->line( '                     -----> OK' );
			$this->line( '' );
			sleep(1);
		}

		$this->line(' finished!');
		$this->line( '' );
		$this->line('Local Time: '.date('Y-m-d H:i:s'));
		$this->line('GMT: '.gmdate('Y-m-d H:i:s'));
		$this->comment('------------ '.$this->signature.' successful ------------');
		$this->line( '' );

		return 0; // 終了コード
	}

}
