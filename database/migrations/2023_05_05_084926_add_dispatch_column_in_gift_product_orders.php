<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDispatchColumnInGiftProductOrders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('gift_product_orders', function (Blueprint $table) {
            $table->integer('cash_courier_service_id')->default(0)->after('status');
			$table->string('cash_track_id')->default('')->after('courier_service_id');;
			$table->string('cash_dispatch_detail', 1000)->default('')->after('track_id');

            $table->integer('cashback_courier_service_id')->default(0)->after('status');
			$table->string('cashback_track_id')->default('')->after('courier_service_id');;
			$table->string('cashback_dispatch_detail', 1000)->default('')->after('track_id');
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
