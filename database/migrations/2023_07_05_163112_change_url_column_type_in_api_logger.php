<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeUrlColumnTypeInApiLogger extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('api_logger', function (Blueprint $table) {
            $table->longText('url')->change();
            $table->longText('method')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('api_logger', function (Blueprint $table) {
            $table->text('url')->change();
            $table->text('method')->change();
        });
    }
}
