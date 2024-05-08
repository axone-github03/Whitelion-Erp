<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnGstPerAmountInWlmstItemPrices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wlmst_item_prices', function (Blueprint $table) {
            $table->decimal('sgst_per',10,2)->default(0);
            $table->decimal('cgst_per',10,2)->default(0);
            $table->decimal('igst_per',10,2)->default(0);
            $table->decimal('sgst_amount',16,2)->default(0);
            $table->decimal('cgst_amount',16,2)->default(0);
            $table->decimal('igst_amount',16,2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('wlmst_item_prices', function (Blueprint $table) {
            //
        });
    }
}
