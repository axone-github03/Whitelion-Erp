<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CrmHelpDocument extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		//
		Schema::create('crm_help_document', function (Blueprint $table) {
			$table->id();
			$table->string('title')->default('');
			$table->dateTime('publish_date_time');
			$table->string('file_name', 500)->default('');
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
