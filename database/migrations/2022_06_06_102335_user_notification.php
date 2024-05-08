<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UserNotification extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		//

		Schema::create('user_notifications', function (Blueprint $table) {
			$table->id();

			$table->unsignedBigInteger('user_id');
			$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
			$table->bigInteger('type')->default(0);
			$table->bigInteger('from_user_id')->default(0);
			$table->string('title', 1000)->default('');
			$table->string('description', 4000)->default('');
			$table->tinyInteger('is_read')->default(0);
			$table->tinyInteger('is_favourite')->default(0);
			$table->bigInteger('inquiry_id')->default(0);
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
