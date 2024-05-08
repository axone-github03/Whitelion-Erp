<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MultipleSourceTypes extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		//

		Schema::table('inquiry', function (Blueprint $table) {

			$table->string('source_type_lable_1')->default('');
			$table->string('source_type_1')->default('');
			$table->string('source_type_value_1')->default('');

			$table->string('source_type_lable_2')->default('');
			$table->string('source_type_2')->default('');
			$table->string('source_type_value_2')->default('');

			$table->string('source_type_lable_3')->default('');
			$table->string('source_type_3')->default('');
			$table->string('source_type_value_3')->default('');

			$table->string('source_type_lable_4')->default('');
			$table->string('source_type_4')->default('');
			$table->string('source_type_value_4')->default('');

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
