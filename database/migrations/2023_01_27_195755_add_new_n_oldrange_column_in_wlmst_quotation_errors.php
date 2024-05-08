<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewNOldrangeColumnInWlmstQuotationErrors extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wlmst_quotation_errors', function (Blueprint $table) {
            $table->integer('old_range')->default(0)->after('old_item_price_id');
            $table->integer('new_range')->default(0)->after('old_range');
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
