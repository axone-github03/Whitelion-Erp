<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class InquiryArchitectElectrician extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		//

		Schema::table('inquiry', function (Blueprint $table) {

			$table->bigInteger('electrician')->default(0)->after('assigned_to');
			$table->bigInteger('architect')->default(0)->after('assigned_to');

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
