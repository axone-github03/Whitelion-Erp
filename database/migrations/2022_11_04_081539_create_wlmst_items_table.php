<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWlmstItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wlmst_items', function (Blueprint $table) {
            $table->id();
            $table->string('itemname')->default('');
            $table->unsignedBigInteger('itemcategory_id')->default(0);
            $table->foreign('itemcategory_id')->references('id')->on('wlmst_item_categories')->onDelete('cascade');
            $table->string('shortname')->default('');
            $table->bigInteger('module')->default(0);
            $table->tinyInteger('isactive')->default(1);
            $table->string('remark')->default('');
            $table->dateTime('created_at')->nullable();
            $table->integer('entryby')->default(0);
            $table->string('entryip', 20)->default('');
            $table->dateTime('updated_at')->nullable();
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
        Schema::dropIfExists('wlmst_items');
    }
}
