<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class InquiryTele extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('inquiry', function (Blueprint $table) {
            $table->tinyInteger('is_for_tele_sale')->default(0)->after('material_sent_channel_partner');
            $table->tinyInteger('is_for_manager')->default(0)->after('material_sent_channel_partner');
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
