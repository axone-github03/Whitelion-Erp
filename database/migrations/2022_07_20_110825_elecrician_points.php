<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ElecricianPoints extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		//

		Schema::table('electrician', function (Blueprint $table) {

			$table->bigInteger('total_inquiry')->default(0)->after('sale_person_id');
			$table->bigInteger('total_site_completed')->default(0)->after('total_inquiry');
			$table->bigInteger('total_point')->default(0)->after('total_site_completed');
			$table->bigInteger('total_point_used')->default(0)->after('total_point');;
			$table->bigInteger('total_point_current')->default(0)->after('total_point_used');
			$table->tinyInteger('converted_prime')->default(0)->after('total_point_current');

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
