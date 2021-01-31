<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class Publish implements ShouldQueue
{
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	private $project_code;
	private $branch_name;

	/**
	 * Create a new job instance.
	 *
	 * @return void
	 */
	public function __construct( $project_code, $branch_name )
	{
		$this->project_code = $project_code;
		$this->branch_name = $branch_name;
	}

	/**
	 * Execute the job.
	 *
	 * @return void
	 */
	public function handle()
	{

		// TODO: 実際のパブリッシュ処理を実装すること。
		ob_start();var_dump('=-=-=-=-=-=-=-=');error_log(ob_get_clean(),3,__DIR__.'/__dump.txt');
		ob_start();var_dump(__LINE__);error_log(ob_get_clean(),3,__DIR__.'/__dump.txt');
		ob_start();var_dump($this->project_code);error_log(ob_get_clean(),3,__DIR__.'/__dump.txt');
		ob_start();var_dump($this->branch_name);error_log(ob_get_clean(),3,__DIR__.'/__dump.txt');
		sleep(1);
		ob_start();var_dump(__LINE__);error_log(ob_get_clean(),3,__DIR__.'/__dump.txt');
		sleep(1);
		ob_start();var_dump(__LINE__);error_log(ob_get_clean(),3,__DIR__.'/__dump.txt');
		sleep(1);
		ob_start();var_dump(__LINE__);error_log(ob_get_clean(),3,__DIR__.'/__dump.txt');
		sleep(1);
		ob_start();var_dump(__LINE__);error_log(ob_get_clean(),3,__DIR__.'/__dump.txt');
		return;
	}
}
