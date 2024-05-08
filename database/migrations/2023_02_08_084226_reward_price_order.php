<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RewardPriceOrder extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('gift_product_order_items', function (Blueprint $table) {

            $table->decimal('item_value', 10, 2)->default(0)->after('cashback');
            $table->decimal('total_item_value', 10, 2)->default(0)->after('cashback');
        });

        Schema::table('gift_product_orders', function (Blueprint $table) {


            $table->decimal('total_item_value', 10, 2)->default(0)->after('total_cashback');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
