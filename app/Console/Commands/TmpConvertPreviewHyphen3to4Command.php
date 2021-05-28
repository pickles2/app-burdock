<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Project;
use App\EventLog;
use App\Helpers\applock;

class TmpConvertPreviewHyphen3to4Command extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'bd:tmp:convert_preview_hyphen_3to4';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'プレビュー環境の区切り文字 ハイフン3つ を 4つ に変更します。 v0.0.x から v0.1.x にアップデートする際に 1度だけ実行します。';

	/** BD_DATA_DIR */
	private $realpath_repositories_dir;


	/** $fs */
	private $fs;

	/** preview dir list */
	private $list_preview_dirs = array();


	/** 一時ファイルのファイル名 */
	private $vhosts_tmp_filename;

	/** 基本認証のデフォルトパスワードファイル */
	private $realpath_basicauth_default_htpasswd;

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();

		$this->fs = new \tomk79\filesystem();

		$this->realpath_repositories_dir = config('burdock.data_dir').'/repositories/';
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

		if( !is_dir($this->realpath_repositories_dir) ){
			mkdir($this->realpath_repositories_dir);
		}

		$prevew_dirs = $this->fs->ls( $this->realpath_repositories_dir );
		foreach( $prevew_dirs as $prevew_dir ){
			$this->info('--- '.$prevew_dir);
			if( preg_match( '/^(.*?)\-{4,}(.*)$/', $prevew_dir ) ){
				$this->line( 'skip' );
				$this->line( '' );
				continue;
			}
			if( !preg_match( '/^(.*?)\-{3,}(.*)$/', $prevew_dir, $matched ) ){
				$this->line( 'skip' );
				$this->line( '' );
				continue;
			}
			$project_code = $matched[1];
			$branch_name = $matched[2];
			$new_dir_name = $project_code.'----'.$branch_name;
			$this->line('rename: '.$prevew_dir.' ===> '.$new_dir_name);
			if(
				$this->fs->rename(
					$this->realpath_repositories_dir.$prevew_dir,
					$this->realpath_repositories_dir.$new_dir_name
				)
			){
				$this->line( 'done.' );
			}else{
				$this->error( 'failed.' );
			}
			$this->line( '' );
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
