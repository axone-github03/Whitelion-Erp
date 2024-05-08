<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBillItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bill_items', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->default(0);
            $table->bigInteger('channel_partner_user_id')->default(0);
            $table->bigInteger('bill_id')->default(0);
            $table->bigInteger('product_inventory_id')->default(0);
            $table->bigInteger('qty')->default(0);
            $table->bigInteger('pending_qty')->default(0);
            $table->bigInteger('cancelled_qty')->default(0);
            $table->bigInteger('dispatched_qty')->default(0);
            $table->decimal('mrp', 10, 2)->default(0.00);
            $table->decimal('total_mrp', 10, 2)->default(0.00);
            $table->decimal('discount_percentage', 10, 2)->default(0.00);
            $table->decimal('discount', 10, 2)->default(0.00);
            $table->decimal('total_discount', 10, 2)->default(0.00);
            $table->decimal('mrp_minus_disocunt', 10, 2)->default(0.00);
            $table->decimal('weight', 10, 2)->default(0.00);
            $table->decimal('total_weight', 10, 2)->default(0.00);
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
        Schema::dropIfExists('bill_items');
    }
}
