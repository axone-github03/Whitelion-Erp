<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChannelPartnerVerified extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		//
		Schema::table('channel_partner', function (Blueprint $table) {

			$table->tinyInteger('data_verified')->default(0)->after('d_city_id');
			$table->tinyInteger('data_not_verified')->default(0)->after('d_city_id');
			$table->tinyInteger('missing_data')->default(0)->after('d_city_id');
			$table->tinyInteger('tele_verified')->default(0)->after('d_city_id');
			$table->tinyInteger('tele_not_verified')->default(0)->after('d_city_id');

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
