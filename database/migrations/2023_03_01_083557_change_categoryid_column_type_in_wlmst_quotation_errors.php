<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeCategoryidColumnTypeInWlmstQuotationErrors extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wlmst_quotation_errors', function (Blueprint $table) {
            $table->string('old_itemcategory_id',50)->nullable()->change();
            $table->string('new_itemcategory_id',50)->nullable()->change();
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
