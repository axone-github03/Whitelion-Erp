<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnRoomnamePlatenamePlatenoInWltrnQuotItemdetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wltrn_quot_itemdetails', function (Blueprint $table) {
            $table->dropColumn('floorno');
            $table->dropColumn('roomno');
            $table->dropColumn('detail_id');
            $table->dropColumn('module_id');

            $table->integer('floor_no')->default(0);
            $table->string('floor_name')->nullable();
            $table->integer('room_no')->default(0);
            $table->string('room_name')->nullable();
            $table->integer('board_no')->default(0);
            $table->string('board_name')->nullable();
            $table->integer('isactiveroom')->default(1);
            $table->integer('isactiveboard')->default(1);
            $table->integer('copyfromroom_no')->default(0);
            $table->integer('copyfromboard_no')->default(0);
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
