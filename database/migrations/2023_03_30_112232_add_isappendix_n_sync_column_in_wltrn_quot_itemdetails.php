<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsappendixNSyncColumnInWltrnQuotItemdetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wltrn_quot_itemdetails', function (Blueprint $table) {
            // $table->integer('is_appendix')->default(0)->after('net_amount');
            // $table->integer('is_sync')->default(0)->after('is_appendix');
            // $table->dateTime('sync_date')->nullable()->after('is_sync');
            // $table->string('sync_ip',20)->nullable()->after('sync_date');
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
