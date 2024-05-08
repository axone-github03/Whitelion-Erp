<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdatePointSystem extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		//
		Schema::table('inquiry', function (Blueprint $table) {
			$table->tinyInteger('is_point_calculated')->default(0)->after('billing_invoice');
			$table->bigInteger('total_point')->default(0)->after('billing_invoice');
			$table->dateTime('claimed_date_time')->nullable();
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
