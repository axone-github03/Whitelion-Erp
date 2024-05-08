<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnLeadIdInNotificationScheduler extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('notification_scheduler', function (Blueprint $table) {
            $table->integer('lead_id')->default(0)->after('attachment');
            $table->integer('point_value')->default(0)->after('lead_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('notification_scheduler', function (Blueprint $table) {
            //
        });
    }
}
