<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeColumnLengthInUserCallAction extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_call_action', function (Blueprint $table) {
            $table->dateTime('call_schedule')->nullable()->change();
            $table->dateTime('reminder')->nullable()->change();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_call_action', function (Blueprint $table) {
            //
        });

    }
}
