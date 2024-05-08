<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReminderColumnInLeadCalls extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lead_calls', function (Blueprint $table) {
            $table->tinyInteger('is_notification')->default(0)->after('is_closed');
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
        Schema::table('lead_calls', function (Blueprint $table) {
            //
        });
    }
}
