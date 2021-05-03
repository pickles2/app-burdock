<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEventLogsTable20210503 extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('event_logs', function (Blueprint $table) {
			$table->timestamps();
			$table->softDeletes();

			$table->uuid('user_id', 36)->nullable();
			$table->uuid('project_id', 36)->nullable();
			$table->string('branch_name')->nullable();
			$table->string('pid')->nullable(); // プロセスID

			$table->string('function_name')->nullable(); // 機能名 (publish, clearcache, composer, indigo, git など)
			$table->string('event_name')->nullable(); // イベント名 (create, delete, publish, clear, install, update, release など)
			$table->string('progress')->nullable(); // イベントの進行状況 (start = 開始, progress = 進捗中, exit = 完了)
			$table->string('message')->nullable(); // 自然言語によるメッセージ

			$table->json('params')->nullable(); // その他、このイベントに渡されたパラメータなどの記録(任意のJSON型で)


			$table->foreign('user_id')->references('id')->on('users'); // foreignkey制約
			$table->foreign('project_id')->references('id')->on('projects'); // foreignkey制約
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('event_logs');
	}
}
