<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeColumnNameInGiftProductOrders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('gift_product_orders', function (Blueprint $table) {
            $table->renameColumn('cash_track_id', 'cash_transaction_id');
            $table->renameColumn('cashback_track_id', 'cashback_transaction_id');
            $table->renameColumn('cash_dispatch_detail', 'cash_document');
            $table->renameColumn('cashback_dispatch_detail', 'cashback_document');
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
