<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReminderIdColumnInLeadTasks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lead_tasks', function (Blueprint $table) {
            $table->integer('reminder_id')->default(1)->after('reminder');
        });

        Schema::table('lead_calls', function (Blueprint $table) {
            $table->integer('reminder_id')->default(1)->after('reminder');
        });

        Schema::table('lead_meetings', function (Blueprint $table) {
            $table->integer('reminder_id')->default(1)->after('reminder');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lead_tasks', function (Blueprint $table) {
            //
        });

        Schema::table('lead_calls', function (Blueprint $table) {
        });

        Schema::table('lead_meetings', function (Blueprint $table) {
        });
    }
}
