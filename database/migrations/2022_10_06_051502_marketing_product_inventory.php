<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MarketingProductInventory extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		//

		Schema::create('marketing_product_inventory', function (Blueprint $table) {

			$table->id();
			$table->unsignedBigInteger('marketing_product_group_id');
			$table->foreign('marketing_product_group_id')->references('id')->on('data_master')->onDelete('cascade');
			$table->unsignedBigInteger('marketing_product_code_id');
			$table->foreign('marketing_product_code_id')->references('id')->on('data_master')->onDelete('cascade');
			$table->string('description')->default('');
			$table->string('hsn')->default('');
			$table->string('image', 200)->default('default.png');
			$table->string('thumb', 200)->default('default.png');
			$table->bigInteger('quantity')->default(0);
			$table->bigInteger('purchase_price')->default(0);
			$table->bigInteger('sale_price')->default(0);
			$table->bigInteger('weight')->default(0);
			$table->tinyInteger('status')->default(1);
			$table->tinyInteger('has_warning')->default(1);
			$table->string('warning')->default('');
			$table->tinyInteger('notify_when_order')->default(0);
			$table->string('notify_emails')->default('');
			$table->tinyInteger('has_specific_code')->default(0);
			$table->decimal('gst_percentage', 10, 2);
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
