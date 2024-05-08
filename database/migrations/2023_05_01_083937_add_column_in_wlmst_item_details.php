<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnInWlmstItemDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wlmst_item_details', function (Blueprint $table) {
            $table->integer('wl_plug')->default(0)->after('special');
            $table->integer('other_plug')->default(0)->after('socket');
            $table->integer('wl_accessories')->default(0)->after('other_plug');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('wlmst_item_details', function (Blueprint $table) {
            //
        });
    }
}
