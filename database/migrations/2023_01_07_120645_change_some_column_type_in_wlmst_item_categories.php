<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeSomeColumnTypeInWlmstItemCategories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wlmst_item_categories', function (Blueprint $table) {
            $table->string('remark')->nullable(true)->change();
            $table->string('itemcategoryname')->nullable(false)->change();
            $table->string('shortname')->nullable(false)->change();
            $table->string('entryip',20)->nullable(true)->default('0')->change();
            $table->string('updateip',20)->nullable(true)->default('0')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('wlmst_item_categories', function (Blueprint $table) {
            //
        });
    }
}
