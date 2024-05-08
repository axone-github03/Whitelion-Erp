<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class GiftProducts extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		//
		Schema::create('gift_products', function (Blueprint $table) {
			$table->id();
			$table->unsignedBigInteger('gift_category_id');
			$table->foreign('gift_category_id')->references('id')->on('gift_categories')->onDelete('cascade');
			$table->string('name')->default('');
			$table->string('image')->default('');
			$table->bigInteger('point_value')->default(0);
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
