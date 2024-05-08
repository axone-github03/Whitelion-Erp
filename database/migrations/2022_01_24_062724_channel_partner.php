<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChannelPartner extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		//

		Schema::create('channel_partner', function (Blueprint $table) {
			$table->id();
			$table->unsignedBigInteger('user_id');
			$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
			$table->integer('type')->default(0);
			$table->string('firm_name')->default('');
			$table->bigInteger('reporting_manager_id')->default(0);
			$table->bigInteger('reporting_company_id')->default(0);
			$table->string('sale_persons')->default('');
			$table->tinyInteger('payment_mode')->default(0);
			$table->integer('credit_days')->default(0);
			$table->bigInteger('credit_limit')->default(0);
			$table->decimal('pending_credit', 10, 2)->default(0);;
			$table->string('gst_number')->default('');
			$table->bigInteger('shipping_limit')->default(0);
			$table->bigInteger('shipping_cost')->default(0);
			$table->string('d_address_line1')->default('');
			$table->string('d_address_line2')->default('');
			$table->string('d_pincode', 20)->default('');
			$table->bigInteger('d_country_id')->default(0);
			$table->bigInteger('d_state_id')->default(0);
			$table->bigInteger('d_city_id')->default(0);
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
