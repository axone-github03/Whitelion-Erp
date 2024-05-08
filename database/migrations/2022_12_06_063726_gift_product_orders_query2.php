<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class GiftProductOrdersQuery2 extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		//
		Schema::create('gift_product_orders_query', function (Blueprint $table) {
			$table->id();

			$table->unsignedBigInteger('gift_product_order_id');
			$table->foreign('gift_product_order_id')->references('id')->on('gift_product_orders')->onDelete('cascade');

			$table->tinyInteger('status')->default(1);

			$table->string('title')->default('');
			$table->string('description')->default('');
			$table->BigInteger('message_from_management')->default(0);
			$table->BigInteger('message_from_crm_user')->default(0);
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
