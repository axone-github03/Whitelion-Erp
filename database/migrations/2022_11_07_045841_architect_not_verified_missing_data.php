<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ArchitectNotVerifiedMissingData extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		//
		Schema::table('architect', function (Blueprint $table) {

			$table->tinyInteger('data_not_verified')->default(0)->after('data_verified');
			$table->tinyInteger('missing_data')->default(0)->after('data_verified');

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
