<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterProject20190619 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE '.env('DB_PREFIX').'projects ADD project_code VARCHAR(36) NOT NULL UNIQUE AFTER id;');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('ALTER TABLE '.env('DB_PREFIX').'projects DROP COLUMN project_code;');
    }
}
