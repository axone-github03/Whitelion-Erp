<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class GiftOrder extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		//

		Schema::table('gift_product_orders', function (Blueprint $table) {
			//
			$table->BigInteger('total_cash')->default(0)->after('total_point_value');
			$table->BigInteger('total_cashback')->default(0)->after('total_point_value');
			$table->BigInteger('cash_point_value')->default(0)->after('total_point_value');
			$table->BigInteger('product_point_value')->default(0)->after('total_point_value');
		});

		Schema::table('gift_product_order_items', function (Blueprint $table) {
			//
			// $table->BigInteger('cash')->default(0)->after('total_point_value');
			// $table->BigInteger('total_cash')->default(0)->after('total_point_value');
			$table->BigInteger('cashback')->default(0)->after('total_point_value');
			$table->BigInteger('total_cashback')->default(0)->after('total_point_value');
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
