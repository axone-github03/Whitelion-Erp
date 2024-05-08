<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnBoardItemidPriceidInWltrnQuotItemdetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wltrn_quot_itemdetails', function (Blueprint $table) {
            $table->integer('board_item_id')->default(0);
            $table->integer('board_item_price_id')->default(0);
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
