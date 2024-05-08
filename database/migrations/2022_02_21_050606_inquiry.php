<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Inquiry extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create('inquiry', function (Blueprint $table) {
			$table->id();
			$table->bigInteger('user_id')->default(0);
			$table->bigInteger('assigned_to')->default(0);
			$table->string('first_name');
			$table->string('last_name');
			$table->string('phone_number', 100);
			$table->string('house_no')->default('');
			$table->string('society_name')->default('');
			$table->string('area')->default('');
			$table->string('pincode', 20)->default('');
			$table->bigInteger('city_id')->default(0);
			$table->tinyInteger('status')->default(0);
			$table->string('source_type_lable')->default('');
			$table->string('source_type')->default('');
			$table->string('source_type_value')->default('');
			$table->string('follow_up_type', 100)->default('');
			$table->dateTime('follow_up_date_time')->nullable();
			$table->string('architect_name', 255)->default('');
			$table->string('architect_phone_number', 20)->default('');
			$table->string('electrician_name', 255)->default('');
			$table->string('electrician_phone_number', 20)->default('');
			$table->string('quotation', 50)->default('');;
			$table->string('quotation_amount', 20)->default('');;
			$table->string('stage_of_site', 200)->default('');
			$table->string('required_for_property', 200)->default('');
			$table->string('site_photos', 200)->default('');
			$table->string('changes_of_closing_order', 200)->default('');
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
