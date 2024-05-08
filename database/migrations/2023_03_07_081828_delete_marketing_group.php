<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DeleteMarketingGroup extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		//

		Schema::table('marketing_product_inventory', function (Blueprint $table) {

			$table->dropForeign('marketing_product_inventory_marketing_product_group_id_foreign');
			$table->dropColumn('marketing_product_group_id');

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
