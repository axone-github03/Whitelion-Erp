<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class GiftProductOrdersQuery3 extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {

		Schema::create('gift_product_orders_query_conversion', function (Blueprint $table) {
			$table->id();

			$table->unsignedBigInteger('gift_product_order_query_id');
			//$table->foreign('gift_product_order_query_id')->references('id')->on('gift_product_orders_query')->onDelete('cascade');

			$table->unsignedBigInteger('from_user_id');
			$table->foreign('from_user_id')->references('id')->on('users')->onDelete('cascade');

			$table->string('message')->default('');
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
