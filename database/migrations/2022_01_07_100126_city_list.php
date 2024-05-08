<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CityList extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		//

		Schema::create('city_list', function (Blueprint $table) {
			$table->id();

			$table->unsignedBigInteger('country_id');
			$table->foreign('country_id')->references('id')->on('country_list')->onDelete('cascade');

			$table->unsignedBigInteger('state_id');
			$table->foreign('state_id')->references('id')->on('state_list')->onDelete('cascade');

			$table->string('name')->default('');

			$table->tinyInteger('status')->default(1);
			$table->timestamps();

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
