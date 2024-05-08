<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DataMaster extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		//
		Schema::create('data_master', function (Blueprint $table) {
			$table->id();

			$table->unsignedBigInteger('main_master_id');
			$table->foreign('main_master_id')->references('id')->on('main_master')->onDelete('cascade');

			$table->string('name')->default('');
			$table->string('code')->default('');
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
