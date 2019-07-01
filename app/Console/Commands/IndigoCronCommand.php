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

		$this->info('================================');
		$this->info('Start indigo:cron');
		$this->info('--------------------------------');

		$indigoController = new DeliveryController();

		$projects = Project::all();
		if( !$projects ){
			$this->error('Failed to load Project list.');
			return 1;
		}


		$progressbar = $this->output->createProgressBar(count($projects));
		$progressbar->start();
		foreach ($projects as $project) {
			$this->line( '' );
			$this->info( $project->project_name );
			$this->line( '('.$project->id.')' );

			$default_branch_name = \get_git_remote_default_branch_name($project->git_url);

			$parameter = $indigoController->mk_indigo_options($project, $default_branch_name);

			// load indigo\main
			$indigo = new \indigo\main($parameter);

			// 実行する
			$result = $indigo->cron_run();
			$this->line( 'OK' );
			$this->line( '' );


			$progressbar->advance();
			sleep(1);
		}
		$progressbar->finish();

		$this->line(' finished!');
		$this->line("\n\n");
		$this->comment('Command successful');
		$this->line("\n\n");

		return 0; // 終了コード
	}
}
