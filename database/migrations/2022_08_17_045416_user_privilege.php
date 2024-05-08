<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UserPrivilege extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		//

		Schema::create('privilege_user_type', function (Blueprint $table) {
			$table->id();
			$table->unsignedBigInteger('privilege_id');
			$table->foreign('privilege_id')->references('id')->on('privilege')->onDelete('cascade');
			$table->bigInteger('user_type')->default(0);
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
