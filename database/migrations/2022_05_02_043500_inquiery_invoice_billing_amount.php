<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class InquieryInvoiceBillingAmount extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		//
		Schema::table('inquiry', function (Blueprint $table) {

			$table->string('billing_invoice', 200)->after('changes_of_closing_order')->default('');
			$table->string('billing_amount', 200)->after('changes_of_closing_order')->default('');

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
