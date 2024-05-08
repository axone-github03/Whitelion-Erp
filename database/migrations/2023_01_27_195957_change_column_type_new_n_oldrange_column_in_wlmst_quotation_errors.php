<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeColumnTypeNewNOldrangeColumnInWlmstQuotationErrors extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wlmst_quotation_errors', function (Blueprint $table) {
            $table->string('old_range',50)->nullable()->change();
            $table->string('new_range',50)->nullable()->change();
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
