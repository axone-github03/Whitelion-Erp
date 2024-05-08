<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddChannelPartnersDiscountInWlmstItemPriceLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wlmst_item_price_logs', function (Blueprint $table) {
            $table->decimal('channel_partners_discount', 10, 2)->default('00.00')->after('discount');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('wlmst_item_price_logs', function (Blueprint $table) {
            //
        });
    }
}