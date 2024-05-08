<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTypeColumnInGiftProductOrders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('gift_product_orders', function (Blueprint $table) {
            $table->tinyInteger('cash_status')->default(0)->after('status');
            $table->tinyInteger('cashback_status')->default(0)->after('cash_status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('gift_product_orders', function (Blueprint $table) {
            //
        });
    }
}
