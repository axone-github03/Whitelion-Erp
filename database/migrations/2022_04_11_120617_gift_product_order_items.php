<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class GiftProductOrderItems extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		//

		Schema::create('gift_product_order_items', function (Blueprint $table) {

			$table->id();

			$table->unsignedBigInteger('user_id');
			$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

			$table->unsignedBigInteger('gift_product_order_id');
			$table->foreign('gift_product_order_id')->references('id')->on('gift_product_orders')->onDelete('cascade');

			$table->unsignedBigInteger('gift_product_id');
			$table->foreign('gift_product_id')->references('id')->on('gift_products')->onDelete('cascade');
			$table->bigInteger('qty')->default(0);
			$table->decimal('point_value', 10, 2);
			$table->decimal('total_point_value', 10, 2);
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
