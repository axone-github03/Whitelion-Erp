<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnRoomBoardDefaultRangeInWltrnQuotItemdetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wltrn_quot_itemdetails', function (Blueprint $table) {
            $table->string('room_range')->nullable();
            $table->string('board_range')->nullable();
        });
        Schema::table('wltrn_quotation', function (Blueprint $table) {
            $table->string('default_range')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('wltrn_quot_itemdetails', function (Blueprint $table) {
            //
        });
    }
}
