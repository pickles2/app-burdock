<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjectsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('projects', function (Blueprint $table) {
			$table->uuid('id', 36)->primary();
			$table->string('project_code');
			$table->string('project_name');
			$table->text('git_url')->nullable();
			$table->text('git_username')->nullable();
			$table->text('git_password')->nullable();
			$table->timestamps();
			$table->uuid('user_id')->nullable();
			$table->foreign('user_id')
				  ->references('id')->on('users')
				  ->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('projects');
	}
}
