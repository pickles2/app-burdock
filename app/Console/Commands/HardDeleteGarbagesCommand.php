<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Project;
use App\User;
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

	/** 論理削除データの保存期間設定 */
	private $softdelete_retention_period;

	/** 各種ログデータの保存期間設定 */
	private $log_retention_period;

	/** ユーザー情報更新用一時テーブルの保存期間設定 */
	private $user_temporary_retention_period;

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();

		$this->fs = new \tomk79\filesystem();

		$now = time();

		// 論理削除データの保存期間設定
		// この時刻よりも前に論理削除されたデータを削除対象とする。
		$this->softdelete_retention_period = $now - (14 * 24 * 60 * 60); // 14日間

		// 各種ログデータの保存期間設定
		$this->log_retention_period = $now - (365 * 24 * 60 * 60); // 1年間

		// ユーザー情報更新用一時テーブルの保存期間設定
		$this->user_temporary_retention_period = $now - (1 * 24 * 60 * 60); // 24時間
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

		// イベントログを記録する
		$this->event_log('start', 'Starting Hard Delete Garbages.');

		// --------------------------------------
		// メールアドレス変更のための一時テーブル
		$affectedRows = DB::table('users_email_changes')
			->where( 'created_at', '<', date('Y-m-d H:i:s', $this->user_temporary_retention_period) )
			->delete();
		$this->event_log('progress', $affectedRows.' records were hard deleted from `users_email_changes`.');

		// --------------------------------------
		// パスワードリセットのための一時テーブル
		$affectedRows = DB::table('password_resets')
			->where( 'created_at', '<', date('Y-m-d H:i:s', $this->user_temporary_retention_period) )
			->delete();
		$this->event_log('progress', $affectedRows.' records were hard deleted from `password_resets`.');


		// --------------------------------------
		// ユーザーデータを物理削除する
		$softDeletedUsers = User::onlyTrashed()
			->where( 'deleted_at', '<', date('Y-m-d H:i:s', $this->softdelete_retention_period) )
			->get();

		foreach( $softDeletedUsers as $user ){
			$this->line( '' );
			$this->info( $user->project_name );
			$this->line( ' ('.$user->id.' - '.$user->name.')' );
			$this->line( ' This project was deleted at '.$user->deleted_at.'.' );


			// ------------
			// DBレコードの物理削除

			// メールアドレス変更のための一時テーブル
			$affectedRows = DB::table('users_email_changes')
				->where( 'user_id', $user->id )
				->delete();
			$this->event_log('progress', $affectedRows.' records were hard deleted from `users_email_changes`.');

			// プロジェクトの作成ユーザーだった場合、 null に置き換える
			$affectedRows = DB::table('projects')
				->where( 'user_id', $user->id )
				->update( [
					'user_id' => null
				] );
			$this->event_log('progress', $affectedRows.' records were set `user_id` to `null` from `projects`.');

			// ユーザー自体の削除
			$user->forceDelete();

			$this->event_log('progress', 'Completed to hard delete user "'.$user->name.' - ('.$user->id.')".');

			$this->line( '' );
			sleep(1);
			continue;
		}



		// --------------------------------------
		// プロジェクトデータを物理削除する
		$softDeletedProjects = Project::onlyTrashed()
			->where( 'deleted_at', '<', date('Y-m-d H:i:s', $this->softdelete_retention_period) )
			->get();

		foreach( $softDeletedProjects as $project ){
			$this->line( '' );
			$this->info( $project->project_name );
			$this->line( ' ('.$project->id.' - '.$project->project_code.')' );
			$this->line( ' This project was deleted at '.$project->deleted_at.'.' );

			$errored_directories = array();
			$removed_directories = array();

			// ------------
			// ステージングを削除
			$this->realpath_stagings_base_dir = config('burdock.data_dir').'/stagings/';
			$dirs = $this->fs->ls( $this->realpath_stagings_base_dir );
			foreach( $dirs as $dirname ){
				if( preg_match('/^'.preg_quote($project->project_code, '/').'\-\-\-\-/', $dirname) ){
					$this->line( ' remove dir: '.$dirname.'' );
					$realpath_dir = $this->fs->get_realpath( $this->realpath_stagings_base_dir.'/'.$dirname.'/' );
					$result = $this->fs->chmod_r( $realpath_dir, 0777 );
					$result = $this->fs->rm( $realpath_dir );
					if( !$result ){
						array_push($errored_directories, $realpath_dir);
					}else{
						array_push($removed_directories, $realpath_dir);
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
					$result = $this->fs->chmod_r( $realpath_dir, 0777 );
					$result = $this->fs->rm( $realpath_dir );
					if( !$result ){
						array_push($errored_directories, $realpath_dir);
					}else{
						array_push($removed_directories, $realpath_dir);
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
					$result = $this->fs->chmod_r( $realpath_dir, 0777 );
					$result = $this->fs->rm( $realpath_dir );
					if( !$result ){
						array_push($errored_directories, $realpath_dir);
					}else{
						array_push($removed_directories, $realpath_dir);
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

				$this->event_log('progress', '[ERROR] Failed to hard delete project "'.$project->project_code.'". Failed to remove any directories. '.implode(', ', $errored_directories), 'error');

				continue;
			}

			// ------------
			// DBレコードの物理削除
			// TODO: リレーションされている周辺テーブルのクリーニング後に実行する
			// $project->forceDelete();

			$this->event_log('progress', 'Completed to hard delete project "'.$project->project_code.'". '.implode(', ', $removed_directories));

			$this->line( '' );
			sleep(1);
			continue;
		}


		// --------------------------------------
		// 古いログデータを物理削除する

		// ------------
		// DBレコードの物理削除
		$affectedRows = $softDeletedEventLogs = EventLog
			::where( 'created_at', '<', date('Y-m-d H:i:s', $this->log_retention_period) )
			->forceDelete();
		$this->event_log('progress', $affectedRows.' records were hard deleted from EventLog.');






		// イベントログを記録する
		$this->event_log('exit', 'Finished Hard Delete Garbages.');

		$this->line( '' );
		$this->line(' finished!');
		$this->line( '' );

		$this->line('Local Time: '.date('Y-m-d H:i:s'));
		$this->line('GMT: '.gmdate('Y-m-d H:i:s'));
		$this->comment('------------ '.$this->signature.' successful ------------');
		$this->line( '' );

		return 0; // 終了コード
	}

	/**
	 * イベントログを記録する
	 */
	private function event_log( $progress, $message, $error_level = null ){
		// イベントログを記録する
		$eventLog = new EventLog;
		$eventLog->pid = getmypid();
		$eventLog->function_name = 'hard_delete_garbages';
		$eventLog->event_name = 'hard_delete';
		$eventLog->progress = $progress;
		$eventLog->message = $message;
		$eventLog->error_level = $error_level;
		$eventLog->save();
		return;
	}

}
