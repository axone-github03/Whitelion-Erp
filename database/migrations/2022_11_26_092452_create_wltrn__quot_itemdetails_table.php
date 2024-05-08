<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWltrnQuotItemdetailsTable extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create('wltrn_quot_itemdetails', function (Blueprint $table) {
			$table->id();
			$table->integer('quotchart_id')->default(0);
			$table->unsignedBigInteger('quot_id');
			$table->foreign('quot_id')->references('id')->on('wltrn_quotation')->onDelete('cascade');
			$table->bigInteger('quotgroup_id');

			$table->text('floorno', 3)->nullable();
			$table->text('roomno', 3)->nullable();
			$table->integer('srno')->default(0);
			$table->integer('detail_id')->default(0);
			$table->integer('module_id')->default(0);
			$table->integer('ampier')->default(0);
			$table->integer('company_id')->default(0);
			$table->integer('itemgroup_id')->default(0);
			$table->integer('itemsubgroup_id')->default(0);
			$table->integer('itemcategory_id')->default(0);
			$table->integer('item_id')->default(0);
			$table->string('itemcode', 40)->default(0);
			$table->string('itemdescription')->nullable(0);
			$table->string('hsn_code')->nullable();
			$table->integer('qty')->default(0);
			$table->decimal('rate', 10, 2)->default(0);
			$table->decimal('discper', 10, 2)->default(0);
			$table->decimal('discamount', 10, 2)->default(0);
			$table->decimal('grossamount', 10, 2)->default(0);
			$table->decimal('addamount', 10, 2)->default(0);
			$table->decimal('lessamount', 10, 2)->default(0);
			$table->decimal('taxableamount', 10, 2)->default(0);
			$table->decimal('igst_per', 10, 2)->default(0);
			$table->decimal('igst_amount', 10, 2)->default(0);
			$table->decimal('cgst_per', 10, 2)->default(0);
			$table->decimal('cgst_amount', 10, 2)->default(0);
			$table->decimal('sgst_per', 10, 2)->default(0);
			$table->decimal('sgst_amount', 10, 2)->default(0);
			$table->decimal('net_amount', 10, 2)->default(0);
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
		Schema::dropIfExists('wltrn_quot_itemdetails');
	}
}
