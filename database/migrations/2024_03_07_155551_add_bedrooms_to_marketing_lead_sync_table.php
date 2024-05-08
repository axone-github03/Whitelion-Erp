<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBedroomsToMarketingLeadSyncTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('marketing_lead_sync', function (Blueprint $table) {
            $table->string('how_soon_are_you_considering_to_automate_your_house')->nullable();
            $table->string('how_many_bedrooms_does_your_apartment_have')->nullable();
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
