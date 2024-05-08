<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class InquiryUpdateSeen extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		//
		Schema::create('inquiry_update_seen', function (Blueprint $table) {
			$table->id();

			$table->unsignedBigInteger('inquiry_id');
			$table->foreign('inquiry_id')->references('id')->on('inquiry')->onDelete('cascade');

			$table->unsignedBigInteger('inquiry_update_id');
			$table->foreign('inquiry_update_id')->references('id')->on('inquiry_update')->onDelete('cascade');

			$table->unsignedBigInteger('user_id');
			$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
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
