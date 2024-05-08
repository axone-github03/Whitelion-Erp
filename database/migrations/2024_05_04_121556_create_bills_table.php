<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBillsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bill', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->default(0);
            $table->bigInteger('channel_partner_user_id')->default(0);
            $table->bigInteger('lead_id')->default(0);
            $table->bigInteger('quotation_id')->default(0);
            $table->tinyInteger('payment_mode')->default(0);
            $table->string('gst_number', 255)->nullable();
            $table->string('sale_persons', 255)->nullable();
            $table->tinyInteger('status')->default(0);
            $table->tinyInteger('sub_status')->default(0);
            $table->string('invoice', 255)->nullable();
            $table->bigInteger('total_qty')->default(0);
            $table->decimal('total_mrp', 10, 2)->default(0.00);
			$table->decimal('total_discount', 10, 2)->default(0.00);
			$table->decimal('total_mrp_minus_disocunt', 10, 2)->default(0.00);
			$table->decimal('gst_percentage')->default(0.00);
			$table->decimal('gst_tax', 10, 2)->default(0.00);
			$table->decimal('total_weight', 10, 2)->default(0.00);
			$table->decimal('shipping_cost', 10, 2)->default(0.00);
			$table->decimal('delievery_charge', 10, 2)->default(0.00);
			$table->decimal('total_payable', 10, 2)->default(0.00);
			$table->decimal('pending_total_payable', 10, 2)->default(0.00);
			$table->decimal('actual_total_mrp_minus_disocunt', 10, 2)->default(0.00);
			$table->tinyInteger('is_cancelled')->default(0);
			$table->bigInteger('cancelled_total_qty')->default(0);
			$table->bigInteger('dispatched_total_qty')->default(0);
			$table->decimal('dispatched_total_payable', 10, 2)->default(0.00);
            $table->string('bill_address_line1')->default('');
			$table->string('bill_address_line2')->default('');
			$table->string('bill_pincode', 20)->default('');
			$table->bigInteger('bill_country_id')->default(0);
			$table->bigInteger('bill_state_id')->default(0);
			$table->bigInteger('bill_city_id')->default(0);
			$table->string('d_address_line1')->default('');
			$table->string('d_address_line2')->default('');
			$table->string('d_pincode', 20)->default('');
			$table->bigInteger('d_country_id')->default(0);
			$table->bigInteger('d_state_id')->default(0);
			$table->bigInteger('d_city_id')->default(0);
            $table->string('remark', 255)->nullable();
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
        Schema::dropIfExists('bill');
    }
}
