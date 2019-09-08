<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\IndigoCronCommand;
use App\Console\Commands\DeployScriptCommand;
use App\Console\Commands\GenerateVirtualHostsCommand;

class Kernel extends ConsoleKernel
{
	/**
	 * The Artisan commands provided by your application.
	 *
	 * @var array
	 */
	protected $commands = [
		Commands\IndigoCronCommand::class,
		Commands\DeployScriptCommand::class,
		Commands\GenerateVirtualHostsCommand::class
	];

	/**
	 * Define the application's command schedule.
	 *
	 * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
	 * @return void
	 */
	protected function schedule(Schedule $schedule)
	{

		$fs = new \tomk79\filesystem();
		$current_dir = realpath('.');
		chdir( __DIR__.'/../../public/' );
		$realpath_log = $fs->get_realpath(env('BD_DATA_DIR').'/logs/'.gmdate('Ym').'/'.gmdate('d').'/cron-schedule-'.gmdate('Ymd-His').'.log');
		chdir( $current_dir );

		$fs->mkdir_r( dirname($realpath_log) );

		$start_time = time();

		$schedule->call(function () use ($realpath_log) {
				$src = '';
				$src .= "\n";
				$src .= 'Local Time: '.date('Y-m-d H:i:s')."\n";
				$src .= 'GMT: '.gmdate('Y-m-d H:i:s')."\n";
				$src .= "\n";
				file_put_contents($realpath_log, $src, FILE_APPEND);
			})
			->everyMinute()
			->appendOutputTo($realpath_log);

		// --------------------------------------
		// Indig による予約配信処理
		$schedule->command(
				IndigoCronCommand::class,
				[]
			)
			->withoutOverlapping(60)
			->everyMinute()
			->appendOutputTo($realpath_log);


		// --------------------------------------
		// 予約配信の後処理
		$schedule->command(
				DeployScriptCommand::class,
				[]
			)
			->withoutOverlapping(60)
			->everyMinute()
			->appendOutputTo($realpath_log);


		// --------------------------------------
		// VirtualHost設定ファイルの生成処理
		$schedule->command(
				GenerateVirtualHostsCommand::class,
				[]
			)
			->withoutOverlapping(60)
			->everyTenMinutes()
			->appendOutputTo($realpath_log);


		$schedule->call(function () use ($realpath_log, $start_time) {
				$src = '';
				$src .= "\n";
				$src .= 'Local Time: '.date('Y-m-d H:i:s')."\n";
				$src .= 'GMT: '.gmdate('Y-m-d H:i:s')."\n";
				$time = time() - $start_time;
				$src .= 'Total: '.$time.' seconds;'."\n";

				file_put_contents($realpath_log, $src, FILE_APPEND);
			})
			->everyMinute()
			->appendOutputTo($realpath_log);

	}

	/**
	 * Register the commands for the application.
	 *
	 * @return void
	 */
	protected function commands()
	{
		$this->load(__DIR__.'/Commands');

		require base_path('routes/console.php');
	}
}
