<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddThreeColumnInLeadMeetings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lead_meetings', function (Blueprint $table) {
            $table->tinyInteger('is_notification')->default(1)->after('is_closed');
            $table->dateTime('reminder')->nullable()->after('is_notification');
            $table->text('close_note')->nullable()->after('reminder');
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
