<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReminderIdColumnInUserAction extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_call_action', function (Blueprint $table) {
            $table->integer('reminder_id')->default(1)->after('reminder');
            $table->dateTime('start_date_time')->nullable()->after('outcome_type');
            $table->dateTime('end_date_time')->nullable()->after('start_date_time');
        });

        Schema::table('user_meeting_action', function (Blueprint $table) {
            $table->integer('reminder_id')->default(1)->after('reminder');
            $table->dateTime('start_date_time')->nullable()->after('outcome_type');
            $table->dateTime('end_date_time')->nullable()->after('start_date_time');
        });

        Schema::table('user_task_action', function (Blueprint $table) {
            $table->integer('reminder_id')->default(1)->after('reminder');
            $table->dateTime('start_date_time')->nullable()->after('outcome_type');
            $table->dateTime('end_date_time')->nullable()->after('start_date_time');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_call_action', function (Blueprint $table) {
            //
        });

        Schema::table('user_meeting_action', function (Blueprint $table) {
            //
        });

        Schema::table('user_task_action', function (Blueprint $table) {
            //
        });
    }
}
