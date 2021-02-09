<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Project;

class CustomConsoleExtensionsBroadcastCommand extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'bd:custom_console_extensions_broadcast {project_code} {branch_name} {cce_id} {realpathMessageFile}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Custom Console Extensions からのブロードキャスト配信を処理する。';

	/** BD_DATA_DIR */
	private $realpath_vhosts_dir;


	/** $fs */
	private $fs;

	/** preview dir list */
	private $list_preview_dirs = array();

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

		$realpathMessageFile = $this->argument('realpathMessageFile');
		$project_code = $this->argument('project_code');
		$branch_name = $this->argument('branch_name');
		$cce_id = $this->argument('cce_id');
		$this->info($realpathMessageFile);

		$jsonStr = file_get_contents($realpathMessageFile);
		$json = json_decode($jsonStr, true);

		// ブロードキャストイベントに標準出力、標準エラー出力、パース結果を渡す、判定変数、キュー数、アラート配列、経過時間配列、パブリッシュファイルを渡す
		broadcast(new \App\Events\CustomConsoleExtensionsEvent($project_code, $branch_name, $cce_id, $json));

		$this->line('Local Time: '.date('Y-m-d H:i:s'));
		$this->line('GMT: '.gmdate('Y-m-d H:i:s'));
		$this->comment('------------ '.$this->signature.' successful ------------');
		$this->line( '' );

		return 0; // 終了コード
	}

}
