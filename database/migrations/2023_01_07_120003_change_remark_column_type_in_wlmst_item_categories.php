<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeRemarkColumnTypeInWlmstItemCategories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wlmst_item_categories', function (Blueprint $table) {
            $table->string('remark')->nullable()->change();
            $table->string('itemcategoryname')->nullable()->change();
            $table->string('shortname')->nullable()->change();
            $table->string('entryip',20)->nullable()->change();
            $table->string('updateip',20)->nullable()->change();
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
