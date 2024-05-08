<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWlmstQuotDiscountFlowItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wlmst_quot_discount_flow_items', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('dis_flow_id')->default(0);
            $table->bigInteger('user_type')->default(0);
            $table->string('user_id')->nullable();
            $table->decimal('discount', 10, 2);
            $table->integer('status')->default(0);
            $table->text('remark')->nullable();
            $table->integer('entryby')->default(0);
            $table->string('entryip', 20)->nullable();
            $table->integer('updateby')->default(0);
            $table->string('updateip', 20)->default('');
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
        Schema::dropIfExists('wlmst_quot_discount_flow_items');
    }
}
