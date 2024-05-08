<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class GiftProductOrdersQuery extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		//

		Schema::table('gift_product_orders', function (Blueprint $table) {
			//
			$table->BigInteger('gift_product_order_query_id')->default(0)->after('total_point_value');
			$table->BigInteger('message_from_crm_user')->default(0)->after('total_point_value');

		});

		// Schema::create('gift_product_orders_query', function (Blueprint $table) {
		// 	$table->id();
		// 	$table->tinyInteger('status')->default(1);
		// 	$table->string('title')->default('');
		// 	$table->string('description')->default('');
		// 	$table->BigInteger('message_from_management')->default(0);
		// 	$table->BigInteger('message_from_crm_user')->default(0);
		// 	$table->timestamps();

		// });

		// Schema::create('gift_product_orders_query_conversion', function (Blueprint $table) {
		// 	$table->id();

		// 	$table->unsignedBigInteger('from_user_id');
		// 	$table->foreign('from_user_id')->references('id')->on('users')->onDelete('cascade');

		// 	$table->unsignedBigInteger('gift_product_orders_query_id');
		// 	$table->foreign('gift_product_orders_query_id')->references('id')->on('gift_product_orders_query')->onDelete('cascade');

		// 	$table->string('message')->default('');
		// 	$table->timestamps();

		// });

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
