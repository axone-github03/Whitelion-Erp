<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSourceTypeToMarketingLeadSyncTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('marketing_lead_sync', function (Blueprint $table) {
            $table->string('source_type')->nullable();
            $table->string('sub_source')->nullable();
            $table->string('source')->nullable();
            $table->string('assign_to')->nullable();
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
