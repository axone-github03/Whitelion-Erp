<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWlmstItemDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wlmst_item_details', function (Blueprint $table) {
            $table->id();
            $table->integer('item_id')->default(0);
            $table->integer('touch_on_off')->default(0);
            $table->integer('touch_fan_regulator')->default(0);
            $table->integer('high_load')->default(0);
            $table->integer('wifi')->default(0);
            $table->integer('rc2')->default(0);
            $table->integer('normal_switch')->default(0);
            $table->integer('normal_fan_regulator')->default(0);
            $table->integer('socket')->default(0);
            $table->integer('communication')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('wlmst_item_details');
    }
}
