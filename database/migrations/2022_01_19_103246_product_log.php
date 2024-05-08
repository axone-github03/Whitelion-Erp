<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ProductLog extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		//

		Schema::create('product_log', function (Blueprint $table) {
			$table->id();

			$table->unsignedBigInteger('product_inventory_id');
			$table->foreign('product_inventory_id')->references('id')->on('product_inventory')->onDelete('cascade');

			$table->unsignedBigInteger('user_id');
			$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
			$table->bigInteger('request_quantity')->default(0);
			$table->bigInteger('quantity')->default(0);
			$table->string('name')->default('');;
			$table->string('description')->default('');;
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
