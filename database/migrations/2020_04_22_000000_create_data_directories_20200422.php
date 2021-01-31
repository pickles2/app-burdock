<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDataDirectories20200422 extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{

		// データディレクトリを作成しておく。
		$realpath_root = env('BD_DATA_DIR');
		clearstatcache();
		if( !strlen($realpath_root) ){
			trigger_error('BD_DATA_DIR is not set.');
			return;
		}
		if( !is_dir($realpath_root) ){
			trigger_error('BD_DATA_DIR is not a directory.');
			return;
		}

		if( !is_dir($realpath_root.'/vhosts/') ){
			mkdir($realpath_root.'/vhosts/');
			touch($realpath_root.'/vhosts/vhosts.conf');
		}

		if( !is_dir($realpath_root.'/projects/') ){
			mkdir($realpath_root.'/projects/');
		}

		if( !is_dir($realpath_root.'/repositories/') ){
			mkdir($realpath_root.'/repositories/');
		}

		if( !is_dir($realpath_root.'/stagings/') ){
			mkdir($realpath_root.'/stagings/');
		}

		if( !is_dir($realpath_root.'/logs/') ){
			mkdir($realpath_root.'/logs/');
		}
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
	}
}
