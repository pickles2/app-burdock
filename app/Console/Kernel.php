<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\IndigoCronCommand;
use App\Console\Commands\DeployScriptCommand;
use App\Console\Commands\HardDeleteGarbagesCommand;
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
		Commands\HardDeleteGarbagesCommand::class,
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
		$realpath_log = $fs->get_realpath(config('burdock.data_dir').'/logs/'.gmdate('Ym').'/'.gmdate('d').'/cron-schedule-'.gmdate('Ymd-His').'.log');
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
		// Indigo: Indig による予約配信処理
		$schedule->command(
				IndigoCronCommand::class,
				[]
			)
			->onOneServer()
			->withoutOverlapping(60) // 排他ロックの有効期限(分)
			->everyMinute()
			->appendOutputTo($realpath_log);


		// --------------------------------------
		// Burdock: 予約配信の後処理
		$schedule->command(
				DeployScriptCommand::class,
				[]
			)
			->onOneServer()
			->withoutOverlapping(60) // 排他ロックの有効期限(分)
			->everyMinute()
			->appendOutputTo($realpath_log);


		// --------------------------------------
		// Burdock: 論理削除されている古いデータを消去する
		$schedule->command(
				HardDeleteGarbagesCommand::class,
				[]
			)
			// ->onOneServer()
			->withoutOverlapping(60) // 排他ロックの有効期限(分)
			->hourlyAt(17) // 毎時 17分に実行する
			->appendOutputTo($realpath_log);


		// --------------------------------------
		// Burdock: VirtualHost設定ファイルの生成処理
		$schedule->command(
				GenerateVirtualHostsCommand::class,
				[]
			)
			// ->onOneServer()
			->withoutOverlapping(60) // 排他ロックの有効期限(分)
			->hourlyAt(47) // 毎時 47分に実行する
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
