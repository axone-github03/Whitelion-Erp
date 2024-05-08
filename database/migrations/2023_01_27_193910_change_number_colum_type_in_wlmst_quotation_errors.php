<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeNumberColumTypeInWlmstQuotationErrors extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wlmst_quotation_errors', function (Blueprint $table) {
            $table->integer('floorno')->default(0)->change();
            $table->integer('roomno')->default(0)->change();
            $table->integer('boardno')->default(0)->after('roomno')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('wlmst_quotation_errors', function (Blueprint $table) {
            //
        });
    }
}
