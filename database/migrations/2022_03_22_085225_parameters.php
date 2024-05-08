<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Parameters extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		//
		Schema::create('parameter', function (Blueprint $table) {
			$table->id();
			$table->string('name')->default('');
			$table->string('code')->default('');
			$table->string('type')->default('');
			$table->string('description')->default('');
			$table->string('name_value')->default('');
			$table->timestamps();

		});
		DB::table('parameter')->insert(
			array(
				[
					'name' => 'point value',
					'code' => 'point-value',
					'type' => 'number',
					'description' => 'Point Value of CRM',
					'name_value' => '100',
					'created_at' => date('Y-m-d H:i:s'),
					'updated_at' => date('Y-m-d H:i:s'),
				],
			)
		);
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
