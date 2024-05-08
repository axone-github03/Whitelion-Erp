<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ProductInventory extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		//

		Schema::create('product_inventory', function (Blueprint $table) {

			$table->id();
			$table->unsignedBigInteger('product_brand_id');
			$table->foreign('product_brand_id')->references('id')->on('data_master')->onDelete('cascade');
			$table->unsignedBigInteger('product_code_id');
			$table->foreign('product_code_id')->references('id')->on('data_master')->onDelete('cascade');
			$table->string('description')->default('');
			$table->string('image', 200)->default('default.png');
			$table->bigInteger('quantity')->default(0);
			$table->bigInteger('price')->default(0);
			$table->bigInteger('weight')->default(0);
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
