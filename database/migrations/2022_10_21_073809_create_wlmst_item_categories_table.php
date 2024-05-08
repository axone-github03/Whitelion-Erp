<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWlmstItemCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wlmst_item_categories', function (Blueprint $table) {
            $table->id();
            $table->string('itemcategoryname')->default('');
            $table->string('shortname')->default('');
            $table->tinyInteger('isactive')->default(1);
            $table->string('remark')->default('');
            $table->dateTime('entrydate')->nullable();
            $table->integer('entryby')->default(0);
            $table->string('entryip', 20)->default('');
            $table->dateTime('updatedate')->nullable();
            $table->integer('updateby')->default(0);
            $table->string('updateip', 20)->default('');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('wlmst_item_categories');
    }
}
