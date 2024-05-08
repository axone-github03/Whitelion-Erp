<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MarketingOrderItems extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		//

		Schema::create('marketing_order_items', function (Blueprint $table) {

			$table->id();

			$table->unsignedBigInteger('user_id');
			$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

			$table->unsignedBigInteger('channel_partner_user_id');
			$table->foreign('channel_partner_user_id')->references('id')->on('users')->onDelete('cascade');

			$table->unsignedBigInteger('marketing_order_id');
			$table->foreign('marketing_order_id')->references('id')->on('marketing_orders')->onDelete('cascade');

			$table->unsignedBigInteger('marketing_product_inventory_id');
			$table->foreign('marketing_product_inventory_id')->references('id')->on('marketing_product_inventory')->onDelete('cascade');

			$table->decimal('gst_percentage');
			$table->decimal('gst_tax', 10, 2);
			$table->decimal('total_gst_tax', 10, 2);
			$table->bigInteger('qty')->default(0);
			$table->bigInteger('pending_qty')->default(0);
			$table->decimal('purchase_mrp', 10, 2);
			$table->decimal('mrp', 10, 2);
			$table->decimal('total_mrp', 10, 2);
			$table->decimal('discount_percentage', 10, 2);
			$table->decimal('discount', 10, 2);
			$table->decimal('total_discount', 10, 2);
			$table->decimal('mrp_minus_disocunt', 10, 2);
			$table->decimal('weight', 10, 2);
			$table->decimal('total_weight', 10, 2);
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
