<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {

		Schema::create('users', function (Blueprint $table) {
			$table->id();
			$table->integer('type')->default(0);
			$table->string('first_name');
			$table->string('last_name');
			$table->string('email', 100)->unique();
			$table->string('dialing_code', 10)->default('');
			$table->string('phone_number', 100)->unique();
			$table->string('password')->default('');
			$table->string('avatar');
			$table->tinyInteger('status')->default(0);;
			$table->string('reference_type')->default('');
			$table->bigInteger('reference_id')->default(0);
			$table->bigInteger('ctc')->default(0);
			$table->string('address_line1')->default('');
			$table->string('address_line2')->default('');
			$table->string('pincode', 20)->default('');
			$table->bigInteger('country_id')->default(0);
			$table->bigInteger('state_id')->default(0);
			$table->bigInteger('city_id')->default(0);
			$table->dateTime('last_active_date_time');
			$table->dateTime('last_login_date_time');
			$table->rememberToken();
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::dropIfExists('users');
	}
}
