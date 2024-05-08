<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CrmLogInquiryId extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		//

		Schema::table('crm_log', function (Blueprint $table) {

			$table->tinyInteger('is_manually')->default(0)->after('for_user_id');
			$table->BigInteger('inquiry_id')->default(0)->after('for_user_id');
			$table->BigInteger('points')->default(0)->after('for_user_id');
			$table->BigInteger('order_id')->default(0)->after('for_user_id');

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
