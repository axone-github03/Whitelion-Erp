<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Privilege extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		//

		Schema::create('privilege', function (Blueprint $table) {
			$table->id();
			$table->string('name', 1000)->default('');
			$table->bigInteger('parent_id')->default(0);
			$table->string('code')->default('');
			$table->decimal('sequence', 10, 2);
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
