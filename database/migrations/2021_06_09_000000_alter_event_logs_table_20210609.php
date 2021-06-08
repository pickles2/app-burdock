<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterEventLogsTable20210609 extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('event_logs', function (Blueprint $table) {
			$table->text('message')->change();
            $table->string('error_level')->nullable(); // エラーのレベル: null = エラーではない, `notice` = 通知, `warning` = 警告, `error` = エラー, `fatal` = 致命的エラー
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('event_logs', function (Blueprint $table) {
			$table->string('message')->change();
            $table->dropColumn('error_level');
		});
	}
}
