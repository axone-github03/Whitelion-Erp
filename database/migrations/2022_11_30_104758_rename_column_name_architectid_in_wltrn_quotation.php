<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameColumnNameArchitectidInWltrnQuotation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wltrn_quotation', function (Blueprint $table) {
            $table->renameColumn('architech_id', 'architech_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('wltrn_quotation', function (Blueprint $table) {
            //
        });
    }
}
