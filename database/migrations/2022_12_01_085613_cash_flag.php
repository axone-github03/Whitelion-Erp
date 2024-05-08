<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CashFlag extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {

		Schema::table('gift_products', function (Blueprint $table) {
			//
			// $table->tinyInteger('is_cash')->default(0)->after('status');
			// $table->BigInteger('cash')->default(0)->after('status');
			$table->tinyInteger('has_cashback')->default(0)->after('status');
			$table->BigInteger('cashback')->default(0)->after('status');
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
