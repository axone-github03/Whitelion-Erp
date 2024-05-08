<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Invoice extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		//

		Schema::create('invoice', function (Blueprint $table) {
			$table->id();
			$table->unsignedBigInteger('user_id');
			$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

			$table->unsignedBigInteger('order_id');
			$table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
			$table->date('invoice_date');
			$table->string('invoice_number')->default('');
			$table->string('invoice_file')->default('');
			$table->string('dispatch_detail', 5000)->default('');
			$table->string('eway_bill')->default('');
			$table->string('track_id')->default('');
			$table->integer('box_number')->default(0);
			$table->integer('courier_service_id')->default(0);
			$table->bigInteger('total_qty')->default(0);
			$table->decimal('total_mrp', 10, 2);
			$table->decimal('total_discount', 10, 2);
			$table->decimal('total_mrp_minus_disocunt', 10, 2);
			$table->decimal('gst_percentage');
			$table->decimal('gst_tax', 10, 2);
			$table->decimal('total_weight', 10, 2);
			$table->decimal('shipping_cost', 10, 2);
			$table->decimal('delievery_charge', 10, 2);
			$table->decimal('total_payable', 10, 2);
			$table->tinyInteger('status')->default(0);

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
