<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class BankDetailArchitech extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		//
		Schema::table('architect', function (Blueprint $table) {

			$table->string('bank_detail_account', 255)->after('total_point_current')->default('');
			$table->string('bank_detail_ifsc', 255)->after('total_point_current')->default('');
			$table->string('bank_detail_upi', 255)->after('total_point_current')->default('');

		});

		Schema::table('electrician', function (Blueprint $table) {

			$table->string('bank_detail_account', 255)->after('total_point_current')->default('');
			$table->string('bank_detail_ifsc', 255)->after('total_point_current')->default('');
			$table->string('bank_detail_upi', 255)->after('total_point_current')->default('');

		});

		Schema::table('gift_product_orders', function (Blueprint $table) {

			$table->tinyInteger('payment_mode')->after('status')->default(0);
			$table->string('bank_detail_account', 255)->after('status')->default('');
			$table->string('bank_detail_ifsc', 255)->after('status')->default('');
			$table->string('bank_detail_upi', 255)->after('status')->default('');

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
