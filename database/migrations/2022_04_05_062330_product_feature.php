<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ProductFeature extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		//
		Schema::create('product_inventory_feature', function (Blueprint $table) {
			$table->id();
			$table->unsignedBigInteger('product_inventory_id');
			$table->foreign('product_inventory_id')->references('id')->on('product_inventory')->onDelete('cascade');
			$table->string('code')->default('');
			$table->bigInteger('feature_value')->default(0);
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
