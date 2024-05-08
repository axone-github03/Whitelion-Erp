<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsverifyColumnInWlmstProductWarranty extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wlmst_product_warranty', function (Blueprint $table) {
            $table->integer('isverify')->default(0)->after('invoice_image')->comment('0 = not verify & 1 = verify');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('wlmst_product_warranty', function (Blueprint $table) {
            //
        });
    }
}
