<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeColumnTypeInApiLogger extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('api_logger', function (Blueprint $table) {
            $table->longText('body')->change();
            $table->longText('header')->change();
            $table->longText('user_agent')->change();
            $table->longText('remark')->change();
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
            $table->text('body')->change();
            $table->text('header')->change();
            $table->text('user_agent')->change();
            $table->text('remark')->change();
        });
    }
}
