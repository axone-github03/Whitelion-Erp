<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class GiftProductOrdersDispatch extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		//

		Schema::table('gift_product_orders', function (Blueprint $table) {

			$table->integer('courier_service_id')->default(0)->after('status');
			$table->string('track_id')->default('')->after('courier_service_id');;
			$table->string('dispatch_detail', 1000)->default('')->after('track_id');

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
