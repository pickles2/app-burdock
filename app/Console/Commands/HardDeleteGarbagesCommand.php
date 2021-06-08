<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Project;
use App\EventLog;
use App\Helpers\applock;

class HardDeleteGarbagesCommand extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'bd:hard_delete_garbages';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = '論理削除された古いデータを、物理削除します。';

	/** $fs */
	private $fs;

	/** 保存期間設定 */
	private $retention_period;

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();

		$this->fs = new \tomk79\filesystem();

		// 保存期間設定
		// この時刻よりも前に論理削除されたデータを削除対象とする。
		$now = time();
		$this->retention_period = $now - (14 * 24 * 60 * 60); // 14日間
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


		// --------------------------------------
		// ユーザーデータを物理削除する
		// TODO: 実装する

		// --------------------------------------
		// プロジェクトデータを物理削除する
		$softDeletedProjects = Project::onlyTrashed()
			->where( 'deleted_at', '<', date('Y-m-d H:i:s', $this->retention_period) )
			->get();

		foreach( $softDeletedProjects as $project ){
			$this->line( '' );
			$this->info( $project->project_name );
			$this->line( ' ('.$project->id.' - '.$project->project_code.')' );
			$this->line( ' This project was deleted at '.$project->deleted_at.'.' );

			$errored_directories = array();

			// ------------
			// ステージングを削除
			$this->realpath_stagings_base_dir = config('burdock.data_dir').'/stagings/';
			$dirs = $this->fs->ls( $this->realpath_stagings_base_dir );
			foreach( $dirs as $dirname ){
				if( preg_match('/^'.preg_quote($project->project_code, '/').'\-\-\-\-/', $dirname) ){
					$this->line( ' remove dir: '.$dirname.'' );
					$realpath_dir = $this->fs->get_realpath( $this->realpath_stagings_base_dir.'/'.$dirname.'/' );
					// $result = $this->fs->rm( $realpath_dir );
					$result = false;
					if( !$result ){
						array_push($errored_directories, $realpath_dir);
					}
				}
			}

			// ------------
			// プレビューを削除
			$this->realpath_repositories_base_dir = config('burdock.data_dir').'/repositories/';
			$dirs = $this->fs->ls( $this->realpath_repositories_base_dir );
			foreach( $dirs as $dirname ){
				if( preg_match('/^'.preg_quote($project->project_code, '/').'\-\-\-/', $dirname) ){
					$this->line( ' remove dir: '.$dirname.'' );
					$realpath_dir = $this->fs->get_realpath( $this->realpath_repositories_base_dir.'/'.$dirname.'/' );
					// $result = $this->fs->rm( $realpath_dir );
					$result = false;
					if( !$result ){
						array_push($errored_directories, $realpath_dir);
					}
				}
			}

			// ------------
			// プロジェクトディレクトリを削除
			$this->realpath_projects_base_dir = config('burdock.data_dir').'/projects/';
			$dirs = $this->fs->ls( $this->realpath_projects_base_dir );
			foreach( $dirs as $dirname ){
				if( $project->project_code == $dirname ){
					$this->line( ' remove dir: '.$dirname.'' );
					$realpath_dir = $this->fs->get_realpath( $this->realpath_projects_base_dir.'/'.$dirname.'/' );
					// $result = $this->fs->rm( $realpath_dir );
					$result = false;
					if( !$result ){
						array_push($errored_directories, $realpath_dir);
					}
				}
			}

			// ------------
			// ディレクトリの削除に問題がある場合、
			// ここで抜けてエラーを報告する
			if( count($errored_directories) ){
				$this->line( '' );
				$this->error( '[ERROR] Failed to remove any directories.' );
				foreach( $errored_directories as $errored_directory ){
					$this->error( ' - '.$errored_directory );
				}
				$this->line( '' );

				// TODO: ログテーブルにエラー情報を挿入する

				continue;
			}

			// ------------
			// DBレコードの物理削除
			// $softDeletedProject->forceDelete();

			$this->line( '' );
			sleep(1);
			continue;
		}


		// --------------------------------------
		// 古いログデータを物理削除する
		// TODO: 実装する


		$this->line( '' );
		$this->line(' finished!');
		$this->line( '' );

		$this->line('Local Time: '.date('Y-m-d H:i:s'));
		$this->line('GMT: '.gmdate('Y-m-d H:i:s'));
		$this->comment('------------ '.$this->signature.' successful ------------');
		$this->line( '' );

		return 0; // 終了コード
	}

}
