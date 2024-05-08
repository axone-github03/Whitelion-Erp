<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMeetingIntervalTimeColumnInLeadMeetings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lead_meetings', function (Blueprint $table) {
            $table->integer('meeting_interval_time')->nullable()->after('meeting_date_time');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lead_meetings', function (Blueprint $table) {
            //
        });
    }
}
