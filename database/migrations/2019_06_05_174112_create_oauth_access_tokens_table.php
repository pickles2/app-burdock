<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOauthAccessTokensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('oauth_access_tokens', function (Blueprint $table) {
            $table->uuid('user_id')->nullable();
            $table->string('remote_service_name');
            $table->string('remote_user_id')->nullable();
            $table->text('remote_name')->nullable();
            $table->text('remote_email')->nullable();
            $table->text('remote_lang')->nullable();
            $table->text('remote_icon')->nullable();
            $table->text('access_token')->nullable();
            $table->timestamps();
            $table->foreign('user_id')
                  ->references('id')->on('users')
                  ->onDelete('cascade');

			$table->unique(['user_id', 'remote_service_name']); // 複合unique制約
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('oauth_access_tokens');
    }
}
