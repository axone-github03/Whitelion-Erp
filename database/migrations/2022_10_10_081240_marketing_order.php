<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MarketingOrder extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		//

		Schema::create('marketing_orders', function (Blueprint $table) {
			$table->id();

			$table->unsignedBigInteger('user_id');
			$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

			$table->unsignedBigInteger('channel_partner_user_id');
			$table->foreign('channel_partner_user_id')->references('id')->on('users')->onDelete('cascade');
			$table->tinyInteger('payment_mode')->default(0);
			$table->string('gst_number')->default('');
			$table->string('sale_persons')->default('');

			$table->tinyInteger('status')->default(0);
			$table->tinyInteger('sub_status')->default(0);

			$table->string('challan')->default('');
			$table->bigInteger('total_qty')->default(0);

			$table->decimal('total_mrp', 10, 2);
			$table->decimal('total_discount', 10, 2);
			$table->decimal('total_mrp_minus_disocunt', 10, 2);
			// $table->decimal('gst_percentage');
			$table->decimal('gst_tax', 10, 2);
			$table->decimal('total_weight', 10, 2);
			$table->decimal('shipping_cost', 10, 2);
			$table->decimal('delievery_charge', 10, 2);
			$table->decimal('total_payable', 10, 2);
			$table->decimal('pending_total_payable', 10, 2);

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
			$table->string('remark')->default('');

			$table->timestamps();

		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		//
	}
}
