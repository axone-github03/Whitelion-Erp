<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class OrderPartiallyCancelled extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //

        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('dispatched_total_payable', 10, 2)->default(0)->after('pending_total_payable');
            $table->bigInteger('dispatched_total_qty')->default(0)->after('pending_total_payable');
            $table->bigInteger('cancelled_total_qty')->default(0)->after('pending_total_payable');
            $table->tinyInteger('is_cancelled')->default(0)->after('pending_total_payable');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->bigInteger('dispatched_qty')->default(0)->after('pending_qty');
            $table->bigInteger('cancelled_qty')->default(0)->after('pending_qty');
        });

        Schema::table('invoice', function (Blueprint $table) {
            //total_qty calculation missing
            $table->tinyInteger('is_cancelled')->default(0)->after('total_payable');
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
