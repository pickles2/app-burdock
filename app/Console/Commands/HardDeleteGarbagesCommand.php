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

	/** 現在時刻のタイムスタンプ */
	private $now;

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

		$this->now = time();

        $utils = new \App\Helpers\utils();

		// 論理削除データの保存期間設定
		// この時刻よりも前に論理削除されたデータを削除対象とする。
		$this->softdelete_retention_period = $utils->resolve_period_config( config('burdock.softdelete_retention_period') );

		// 各種ログデータの保存期間設定
		$this->log_retention_period = $utils->resolve_period_config( config('burdock.log_retention_period') );

		// ユーザー情報更新用一時テーブルの保存期間設定
		$this->user_temporary_retention_period = $utils->resolve_period_config( config('burdock.user_temporary_retention_period') );
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
		$this->line( 'BD_SOFTDELETE_RETENTION_PERIOD='.config('burdock.softdelete_retention_period').' ('.(is_int($this->softdelete_retention_period) ? $this->softdelete_retention_period.' sec' : 'false').')' );
		$this->line( 'BD_LOG_RETENTION_PERIOD='.config('burdock.log_retention_period').' ('.(is_int($this->log_retention_period) ? $this->log_retention_period.' sec' : 'false').')' );
		$this->line( 'BD_USER_TEMPORARY_RETENTION_PERIOD='.config('burdock.user_temporary_retention_period').' ('.(is_int($this->user_temporary_retention_period) ? $this->user_temporary_retention_period.' sec' : 'false').')' );
		$this->line( '' );
		$this->info('----------------------------------------------------------------');
		$this->line( '' );

		// イベントログの記録を開始する
		$this->event_log('start', 'Starting Hard Delete Garbages.');


		// 保存期間を過ぎたデータを削除する
		$this->hard_delete_user_temporary_data();
		$this->hard_delete_softdelete_data();
		$this->hard_delete_old_log_data();


		// イベントログの記録を終了する
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
	 * 保存期間を過ぎたユーザー情報更新用一時テーブルを削除する
	 */
	private function hard_delete_user_temporary_data(){

		if( !is_int( $this->user_temporary_retention_period ) ){
			return false;
		}

		// --------------------------------------
		// メールアドレス変更のための一時テーブル
		$affectedRows = DB::table('users_email_changes')
			->where( 'created_at', '<', date('Y-m-d H:i:s', $this->now - $this->user_temporary_retention_period) )
			->delete();
		$this->event_log('progress', $affectedRows.' records were hard deleted from `users_email_changes`.');

		// --------------------------------------
		// パスワードリセットのための一時テーブル
		$affectedRows = DB::table('password_resets')
			->where( 'created_at', '<', date('Y-m-d H:i:s', $this->now - $this->user_temporary_retention_period) )
			->delete();
		$this->event_log('progress', $affectedRows.' records were hard deleted from `password_resets`.');

		return;
	}

	/**
	 * 保存期間を過ぎた論理削除データを削除する
	 */
	private function hard_delete_softdelete_data(){

		if( !is_int( $this->softdelete_retention_period ) ){
			return false;
		}

		// --------------------------------------
		// ユーザーデータを物理削除する
		$softDeletedUsers = User::onlyTrashed()
			->where( 'deleted_at', '<', date('Y-m-d H:i:s', $this->now - $this->softdelete_retention_period) )
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
			$this->event_log('progress', $affectedRows.' records were hard deleted from `users_email_changes`. (via: user_id = "'.$user->id.'")');

			// プロジェクトの作成ユーザーだった場合、 null に置き換える
			$affectedRows = DB::table('projects')
				->where( 'user_id', $user->id )
				->update( [
					'user_id' => null
				] );
			$this->event_log('progress', $affectedRows.' records were set `user_id` to `null` from `projects`. (via: user_id = "'.$user->id.'")');

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
			->where( 'deleted_at', '<', date('Y-m-d H:i:s', $this->now - $this->softdelete_retention_period) )
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
			$realpath_stagings_base_dir = config('burdock.data_dir').'/stagings/';
			$dirs = $this->fs->ls( $realpath_stagings_base_dir );
			foreach( $dirs as $dirname ){
				if( preg_match('/^'.preg_quote($project->project_code, '/').'\-\-\-\-/', $dirname) ){
					$this->line( ' remove dir: '.$dirname.'' );
					$realpath_dir = $this->fs->get_realpath( $realpath_stagings_base_dir.'/'.$dirname.'/' );
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
			$realpath_repositories_base_dir = config('burdock.data_dir').'/repositories/';
			$dirs = $this->fs->ls( $realpath_repositories_base_dir );
			foreach( $dirs as $dirname ){
				if( preg_match('/^'.preg_quote($project->project_code, '/').'\-\-\-/', $dirname) ){
					$this->line( ' remove dir: '.$dirname.'' );
					$realpath_dir = $this->fs->get_realpath( $realpath_repositories_base_dir.'/'.$dirname.'/' );
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
			$realpath_projects_base_dir = config('burdock.data_dir').'/projects/';
			$dirs = $this->fs->ls( $realpath_projects_base_dir );
			foreach( $dirs as $dirname ){
				if( $project->project_code == $dirname ){
					$this->line( ' remove dir: '.$dirname.'' );
					$realpath_dir = $this->fs->get_realpath( $realpath_projects_base_dir.'/'.$dirname.'/' );
					$result = $this->fs->chmod_r( $realpath_dir, 0777 );
					$result = $this->fs->rm( $realpath_dir );
					if( !$result ){
						array_push($errored_directories, $realpath_dir);
					}else{
						array_push($removed_directories, $realpath_dir);
					}
				}
			}

			if( count($removed_directories) ){
				$this->event_log('progress', 'Completed to remove any directories. (via: project_code = "'.$project->project_code.'"). '.implode(', ', $removed_directories));
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

			// Indigo関連テーブル
			$affectedRows = DB::table('TS_BACKUP')
				->where( 'space_name', $project->project_code )
				->delete();
			$this->event_log('progress', $affectedRows.' records were hard deleted from `TS_BACKUP`. (via: project_code = "'.$project->project_code.'")');
			$affectedRows = DB::table('TS_OUTPUT')
				->where( 'space_name', $project->project_code )
				->delete();
			$this->event_log('progress', $affectedRows.' records were hard deleted from `TS_OUTPUT`. (via: project_code = "'.$project->project_code.'")');
			$affectedRows = DB::table('TS_RESERVE')
				->where( 'space_name', $project->project_code )
				->delete();
			$this->event_log('progress', $affectedRows.' records were hard deleted from `TS_RESERVE`. (via: project_code = "'.$project->project_code.'")');

			// プロジェクト自体の削除
			$project->forceDelete();

			$this->event_log('progress', 'Completed to hard delete project "'.$project->project_code.'". '.implode(', ', $removed_directories));

			$this->line( '' );
			sleep(1);
			continue;
		}

		return;
	}

	/**
	 * 保存期間を過ぎた各種ログデータを削除する
	 */
	private function hard_delete_old_log_data(){

		if( !is_int( $this->log_retention_period ) ){
			return false;
		}

		// --------------------------------------
		// 古いログデータを物理削除する

		// ------------
		// DBレコードの物理削除
		$affectedRows = $softDeletedEventLogs = EventLog
			::where( 'created_at', '<', date('Y-m-d H:i:s', $this->now - $this->log_retention_period) )
			->forceDelete();
		$this->event_log('progress', $affectedRows.' records were hard deleted from EventLog.');

		// ------------
		// 古いログディレクトリを削除
		$realpath_logs_base_dir = config('burdock.data_dir').'/logs/';
		$dirs = $this->fs->ls( $realpath_logs_base_dir );
		$period_year_month = intval( date('Ym', $this->now - $this->log_retention_period) );
		$period_date = intval( date('j', $this->now - $this->log_retention_period) );
		$removed_directories = array();
		$errored_directories = array();
		foreach( $dirs as $dirname ){
			if( preg_match('/^[1-9][0-9]*$/', $dirname) ){
				$dirname_int = intval($dirname);
				if( $dirname_int < $period_year_month ){
					// 年月が期限日より過去なら、年月ディレクトリごと消去
					$this->line( ' remove dir: '.$dirname.'' );
					$realpath_dir = $this->fs->get_realpath( $realpath_logs_base_dir.'/'.$dirname.'/' );
					$result = $this->fs->chmod_r( $realpath_dir, 0777 );
					$result = $this->fs->rm( $realpath_dir );
					if( !$result ){
						array_push($errored_directories, $realpath_dir);
					}else{
						array_push($removed_directories, $realpath_dir);
					}
				}elseif( $dirname_int == $period_year_month ){
					// 年月が期限日と一致なら、ディレクトリを開いて日ごとに評価

					$subdirs = $this->fs->ls( $realpath_logs_base_dir.'/'.$dirname.'/' );
					foreach( $subdirs as $subdirname ){
						if( preg_match('/^[0-9]{2}$/', $dirname) ){
							$subdirname_int = intval( preg_replace('/^0*/', '', $dirname) );
							if( $subdirname_int < $period_date ){
								// 日が期限日より過去なら、日ディレクトリごと消去
								$this->line( ' remove dir: '.$dirname.'/'.$subdirname );
								$realpath_dir = $this->fs->get_realpath( $realpath_logs_base_dir.'/'.$dirname.'/'.$subdirname.'/' );
								$result = $this->fs->chmod_r( $realpath_dir, 0777 );
								$result = $this->fs->rm( $realpath_dir );
								if( !$result ){
									array_push($errored_directories, $realpath_dir);
								}else{
									array_push($removed_directories, $realpath_dir);
								}
							}else{
								// 年月が期限日と一致、またはより新しければ、何もしない
							}

						}
					}

				}else{
					// 年月が期限日より新しければ、何もしない
				}

			}
		}

		if( count($removed_directories) ){
			$this->event_log('progress', 'Completed to remove old log directories. '.implode(', ', $removed_directories));
		}

		// ------------
		// ディレクトリの削除に問題がある場合、エラーを報告する
		if( count($errored_directories) ){
			$this->line( '' );
			$this->error( '[ERROR] Failed to remove any directories.' );
			foreach( $errored_directories as $errored_directory ){
				$this->error( ' - '.$errored_directory );
			}
			$this->line( '' );

			$this->event_log('progress', '[ERROR] Failed to remove old log directories. '.implode(', ', $errored_directories), 'error');
		}

		return;
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
