<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveColumnGstInWlmstItemPrices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wlmst_item_prices', function (Blueprint $table) {
            $table->dropColumn('sgst_per');
            $table->dropColumn('cgst_per');
            $table->dropColumn('igst_per');
            $table->dropColumn('sgst_amount');
            $table->dropColumn('cgst_amount');
            $table->dropColumn('igst_amount');
            $table->dropColumn('final_mrp');
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
