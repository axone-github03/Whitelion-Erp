<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAmountCalculationColumnInWltrnQuotation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wltrn_quotation', function (Blueprint $table) {
            $table->decimal('quot_whitelion_amount',10,2)->default(0)->after('net_amount');
            $table->decimal('quot_billing_amount',10,2)->default(0)->after('quot_whitelion_amount');
            $table->decimal('quot_other_amount',10,2)->default(0)->after('quot_billing_amount');
            $table->decimal('quot_total_amount',10,2)->default(0)->after('quot_other_amount');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('wltrn_quotation', function (Blueprint $table) {
            //
        });
    }
}
