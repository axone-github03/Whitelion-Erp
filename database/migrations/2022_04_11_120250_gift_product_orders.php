<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class GiftProductOrders extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		//

		Schema::create('gift_product_orders', function (Blueprint $table) {
			$table->id();

			$table->unsignedBigInteger('user_id');
			$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
			$table->decimal('total_point_value', 10, 2);
			$table->string('d_address_line1')->default('');
			$table->string('d_address_line2')->default('');
			$table->string('d_pincode', 20)->default('');
			$table->bigInteger('d_country_id')->default(0);
			$table->bigInteger('d_state_id')->default(0);
			$table->bigInteger('d_city_id')->default(0);
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
