<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Statelist extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		//
		Schema::create('state_list', function (Blueprint $table) {
			$table->id();

			$table->unsignedBigInteger('country_id');
			$table->foreign('country_id')->references('id')->on('country_list')->onDelete('cascade');

			$table->string('name')->default('');
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
