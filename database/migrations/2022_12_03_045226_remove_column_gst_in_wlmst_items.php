<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveColumnGstInWlmstItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wlmst_items', function (Blueprint $table) {
            $table->dropColumn('sgst_per');
            $table->dropColumn('cgst_per');
            $table->dropColumn('igst_per');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('wlmst_items', function (Blueprint $table) {
            //
        });
    }
}
