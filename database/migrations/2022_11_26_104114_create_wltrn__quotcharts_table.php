<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWltrnQuotchartsTable extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create('wltrn_quotcharts', function (Blueprint $table) {
			$table->id();
			$table->unsignedBigInteger('quot_id');
			$table->foreign('quot_id')->references('id')->on('wltrn_quotation')->onDelete('cascade');
			$table->bigInteger('quotgroup_id');

			$table->text('floorno')->nullable();
			$table->text('roomno')->nullable();
			$table->integer('srno')->default(0);
			$table->integer('module_id')->default(0);

			$table->integer('sw_6a')->default(0);
			$table->integer('sw_16a')->default(0);
			$table->integer('hl')->default(0);
			$table->integer('plug_6a')->default(0);
			$table->integer('plug_16a')->default(0);
			$table->integer('dummy')->default(0);
			$table->integer('tv')->default(0);
			$table->integer('usb')->default(0);
			$table->integer('wifi')->default(0);
			$table->integer('remote')->default(0);

			$table->string('remark')->nullable();
			$table->integer('model_id')->default(0);

			$table->dateTime('created_at')->nullable();
			$table->integer('entryby')->default(0);
			$table->string('entryip', 20)->default('');
			$table->dateTime('updated_at')->nullable();
			$table->integer('updateby')->default(0);
			$table->string('updateip', 20)->default('');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::dropIfExists('wltrn_quotcharts');
	}
}
