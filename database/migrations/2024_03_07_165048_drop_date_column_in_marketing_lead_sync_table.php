<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropDateColumnInMarketingLeadSyncTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('marketing_lead_sync', function (Blueprint $table) {
            $table->dropColumn('date');
            $table->dropColumn('telesales_team_remark');
            $table->dropColumn('sales_team_remark');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('marketing_lead_sync', function (Blueprint $table) {
            //
        });
    }
}
