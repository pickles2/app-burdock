<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersEmailChangesTable20210413 extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('users_email_changes', function (Blueprint $table) {
			$table->uuid('user_id', 36);
			$table->string('email')->index();
			$table->string('token');
			$table->string('method')->nullable();
			$table->timestamp('created_at')->nullable();

			$table->foreign('user_id')->references('id')->on('users'); // foreignkey制約
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('users_email_changes');
	}
}
