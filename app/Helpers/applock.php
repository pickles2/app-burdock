<?php
namespace App\Helpers;

use App\Project;

class applock{
	private $project;
	private $project_id;
	private $branch_name;

	private $app_name;
	private $expire = 3600;

	private $realpath_lockdir;
	private $lock_file_name;

	/** $fs */
	private $fs;

	/**
	 * Constructor
	 */
	public function __construct( $app_name, $expire = 3600, $project = null, $branch_name = null ){
		$this->app_name = $app_name;
		if( strlen($expire) ){
			$this->expire = intval($expire);
		}

		if(is_null($project)){
			// Project情報に関連付けないで利用する場合
			return;
		}else if(is_object($project)){
			// Projectモデル を受け取った場合
			$this->project = $project;
			$this->project_id = $project->id;
		}else{
			// Project ID を受け取った場合
			$this->project_id = $project;
			$this->project = Project::find($project);
		}

		$this->branch_name = $branch_name;

		$this->fs = new \tomk79\filesystem();

		$this->realpath_lockdir = env('BD_DATA_DIR').'/applock/';
		if( !$this->fs->is_dir($this->realpath_lockdir) ){
			$this->fs->mkdir($this->realpath_lockdir);
		}

		$this->lock_file_name = urlencode($this->app_name).'.lock.txt';
	}

	/**
	 * アプリケーションロックする。
	 *
	 * @return bool ロック成功時に `true`、失敗時に `false` を返します。
	 */
	public function lock(){
		$lockfilepath = $this->realpath_lockdir.$this->lock_file_name;
		$timeout_limit = 5;

		if( !is_dir( dirname( $lockfilepath ) ) ){
			$this->fs()->mkdir_r( dirname( $lockfilepath ) );
		}

		// PHPのFileStatusCacheをクリア
		clearstatcache();

		$i = 0;
		while( $this->is_locked() ){
			$i ++;
			if( $i >= $timeout_limit ){
				return false;
				break;
			}
			sleep(1);

			// PHPのFileStatusCacheをクリア
			clearstatcache();
		}
		$src = '';
		$src .= 'ProcessID='.getmypid()."\r\n";
		$src .= @date( 'Y-m-d H:i:s' , time() )."\r\n";
		$RTN = $this->fs()->save_file( $lockfilepath , $src );
		return	$RTN;
	} // lock()

	/**
	 * アプリケーションロックされているか確認する。
	 *
	 * @return bool ロック中の場合に `true`、それ以外の場合に `false` を返します。
	 */
	public function is_locked(){
		$lockfilepath = $this->realpath_lockdir.$this->lock_file_name;
		$lockfile_expire = $this->expire;

		// PHPのFileStatusCacheをクリア
		clearstatcache();

		if( $this->fs()->is_file($lockfilepath) ){
			if( ( time() - filemtime($lockfilepath) ) > $lockfile_expire ){
				// 有効期限を過ぎていたら、ロックは成立する。
				return false;
			}
			return true;
		}
		return false;
	} // is_locked()

	/**
	 * アプリケーションロックを解除する。
	 *
	 * @return bool ロック解除成功時に `true`、失敗時に `false` を返します。
	 */
	public function unlock(){
		$lockfilepath = $this->realpath_lockdir.$this->lock_file_name;

		// PHPのFileStatusCacheをクリア
		clearstatcache();
		if( !is_file( $lockfilepath ) ){
			return true;
		}

		return unlink( $lockfilepath );
	} // unlock()

	/**
	 * アプリケーションロックファイルの更新日を更新する。
	 *
	 * @return bool 成功時に `true`、失敗時に `false` を返します。
	 */
	public function touch_lockfile(){
		$lockfilepath = $this->realpath_lockdir.$this->lock_file_name;

		// PHPのFileStatusCacheをクリア
		clearstatcache();
		if( !is_file( $lockfilepath ) ){
			return false;
		}

		return touch( $lockfilepath );
	} // touch_lockfile()

}
