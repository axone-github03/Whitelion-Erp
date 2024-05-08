<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Architect extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		//
		Schema::create('architect', function (Blueprint $table) {
			$table->id();
			$table->unsignedBigInteger('user_id');
			$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
			$table->integer('type')->default(0);
			$table->string('firm_name')->default('');
			$table->bigInteger('sale_person_id')->default(0);
			$table->tinyInteger('is_residential')->default(0);
			$table->tinyInteger('is_commercial_or_office_space')->default(0);
			$table->tinyInteger('interior')->default(0);
			$table->tinyInteger('exterior')->default(0);
			$table->tinyInteger('structural_design')->default(0);
			$table->tinyInteger('practicing')->default(0);
			$table->string('visiting_card')->default('');
			$table->string('aadhar_card')->default('');;
			$table->string('brand_using_for_switch')->default('');;
			$table->string('brand_used_before_home_automation')->default('');;
			$table->tinyInteger('whitelion_smart_switches_before')->default(0);
			$table->string('how_many_projects_used_whitelion_smart_switches')->default('');;
			$table->tinyInteger('experience_with_whitelion')->default(0);
			$table->string('suggestion')->default('');
			$table->bigInteger('total_inquiry')->default(0);
			$table->bigInteger('total_site_completed')->default(0);
			$table->bigInteger('total_point')->default(0);
			$table->bigInteger('total_point_used')->default(0);
			$table->bigInteger('total_point_current')->default(0);
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
