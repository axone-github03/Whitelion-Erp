<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsRescheduleColumnInCrmSettingCallOutcomeType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('crm_setting_call_outcome_type', function (Blueprint $table) {
            $table->tinyInteger('is_reschedule')->default(0)->after('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('crm_setting_call_outcome_type', function (Blueprint $table) {
            //
        });
    }
}
