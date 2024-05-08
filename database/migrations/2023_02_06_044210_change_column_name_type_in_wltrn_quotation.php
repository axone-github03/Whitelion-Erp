<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeColumnNameTypeInWltrnQuotation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wltrn_quotation', function (Blueprint $table) {
            $table->renameColumn('quotationgroup_id', 'quotgroup_id');
            $table->renameColumn('quotationtype_id', 'quottype_id');
            $table->renameColumn('quotationno', 'quotno');
            $table->renameColumn('quotation_no_str', 'quot_no_str');
            $table->renameColumn('quotationdate', 'quot_date');
            $table->renameColumn('siteState_id', 'site_state_id');
            $table->integer('site_city_id')->default(0)->after('siteState_id');
            $table->integer('site_country_id')->default(0)->after('site_city_id');
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
