<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class InvoiceItems extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		//

		Schema::create('invoice_items', function (Blueprint $table) {
			$table->id();
			$table->unsignedBigInteger('user_id');
			$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
			$table->unsignedBigInteger('order_id');
			$table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');

			$table->unsignedBigInteger('order_item_id');
			$table->foreign('order_item_id')->references('id')->on('order_items')->onDelete('cascade');

			$table->unsignedBigInteger('invoice_id');
			$table->foreign('invoice_id')->references('id')->on('invoice')->onDelete('cascade');
			$table->bigInteger('qty')->default(0);
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
