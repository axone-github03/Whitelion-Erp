<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class LeadTaskCloseDateTime extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('lead_calls', function (Blueprint $table) {
            $table->dateTime('closed_date_time')->nullable()->after('is_closed');
        });

        Schema::table('lead_tasks', function (Blueprint $table) {
            $table->dateTime('closed_date_time')->nullable()->after('is_closed');
        });

        Schema::table('lead_meetings', function (Blueprint $table) {
            $table->dateTime('closed_date_time')->nullable()->after('is_closed');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
