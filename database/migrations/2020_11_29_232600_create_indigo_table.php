<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

// Indigo
use indigo\db\tsReserve as tsReserve;
use indigo\db\tsOutput as tsOutput;
use indigo\db\tsBackup as tsBackup;

class CreateIndigoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        if (!Schema::hasTable('TS_RESERVE')) {
            Schema::create('TS_RESERVE', function (Blueprint $table) {
                $table->string( tsReserve::TS_RESERVE_ID_SEQ, 64 )->primary();
                $table->text( tsReserve::TS_RESERVE_DATETIME )->nullable();
                $table->text( tsReserve::TS_RESERVE_BRANCH )->nullable();
                $table->text( tsReserve::TS_RESERVE_COMMIT_HASH )->nullable();
                $table->text( tsReserve::TS_RESERVE_COMMENT )->nullable();
                $table->text( tsReserve::TS_RESERVE_STATUS )->nullable();
                $table->text( tsReserve::TS_RESERVE_DELETE_FLG )->nullable();
                $table->text( tsReserve::TS_RESERVE_INSERT_DATETIME )->nullable();
                $table->text( tsReserve::TS_RESERVE_INSERT_USER_ID )->nullable();
                $table->text( tsReserve::TS_RESERVE_SPACE_NAME )->nullable();
                $table->text( tsReserve::TS_RESERVE_UPDATE_DATETIME )->nullable();
                $table->text( tsReserve::TS_RESERVE_UPDATE_USER_ID )->nullable();
                $table->text( tsReserve::TS_RESERVE_VER_NO )->nullable();
            });
        }


        if (!Schema::hasTable('TS_OUTPUT')) {
            Schema::create('TS_OUTPUT', function (Blueprint $table) {
                $table->string( tsOutput::TS_OUTPUT_ID_SEQ, 64 )->primary();
                $table->string( tsOutput::TS_OUTPUT_RESERVE_ID, 64 )->nullable();
                $table->string( tsOutput::TS_OUTPUT_BACKUP_ID, 64 )->nullable();
                $table->text( tsOutput::TS_OUTPUT_RESERVE )->nullable();
                $table->text( tsOutput::TS_OUTPUT_BRANCH )->nullable();
                $table->text( tsOutput::TS_OUTPUT_COMMIT_HASH )->nullable();
                $table->text( tsOutput::TS_OUTPUT_COMMENT )->nullable();
                $table->text( tsOutput::TS_OUTPUT_PUBLISH_TYPE )->nullable();
                $table->text( tsOutput::TS_OUTPUT_STATUS )->nullable();
                $table->text( tsOutput::TS_OUTPUT_SRV_BK_DIFF_FLG )->nullable();
                $table->text( tsOutput::TS_OUTPUT_START )->nullable();
                $table->text( tsOutput::TS_OUTPUT_END )->nullable();
                $table->text( tsOutput::TS_OUTPUT_GEN_DELETE_FLG )->nullable();
                $table->text( tsOutput::TS_OUTPUT_GEN_DELETE )->nullable();
                $table->text( tsOutput::TS_OUTPUT_INSERT_DATETIME )->nullable();
                $table->text( tsOutput::TS_OUTPUT_INSERT_USER_ID )->nullable();
                $table->text( tsOutput::TS_OUTPUT_SPACE_NAME )->nullable();
                $table->text( tsOutput::TS_OUTPUT_UPDATE_DATETIME )->nullable();
                $table->text( tsOutput::TS_OUTPUT_UPDATE_USER_ID )->nullable();
            });
        }

        if (!Schema::hasTable('TS_BACKUP')) {
            Schema::create('TS_BACKUP', function (Blueprint $table) {
                $table->string( tsBackup::TS_BACKUP_ID_SEQ, 64 )->primary();
                $table->string( tsBackup::TS_BACKUP_OUTPUT_ID, 64 )->nullable();
                $table->text( tsBackup::TS_BACKUP_DATETIME )->nullable();
                $table->text( tsBackup::TS_BACKUP_GEN_DELETE_FLG )->nullable();
                $table->text( tsBackup::TS_BACKUP_GEN_DELETE_DATETIME )->nullable();
                $table->text( tsBackup::TS_BACKUP_INSERT_DATETIME )->nullable();
                $table->text( tsBackup::TS_BACKUP_INSERT_USER_ID )->nullable();
                $table->text( tsBackup::TS_BACKUP_SPACE_NAME )->nullable();
                $table->text( tsBackup::TS_BACKUP_UPDATE_DATETIME )->nullable();
                $table->text( tsBackup::TS_BACKUP_UPDATE_USER_ID )->nullable();
            });
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
		Schema::dropIfExists('TS_RESERVE');
    }
}
