<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPrimeArchitectDetail extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		//

		Schema::table('architect', function (Blueprint $table) {

			$table->string('principal_architect_name', 255)->default('')->after('prime_nonprime_date');
			$table->string('instagram_link', 255)->default('')->after('prime_nonprime_date');
			$table->tinyInteger('data_verified')->default(0);
			$table->tinyInteger('tele_verified')->default(0);

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
