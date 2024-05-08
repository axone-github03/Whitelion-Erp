<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class InquiryCommentCountAndUpdate extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		//
		Schema::table('inquiry', function (Blueprint $table) {

			$table->integer('update_count')->default(0)->after('billing_invoice');
			$table->dateTime('last_update')->nullable()->after('billing_invoice');
			$table->string('monday_dot_com_id', 255)->default('')->after('billing_invoice');

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
