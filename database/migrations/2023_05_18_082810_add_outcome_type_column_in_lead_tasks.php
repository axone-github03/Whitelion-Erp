<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOutcomeTypeColumnInLeadTasks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lead_tasks', function (Blueprint $table) {
            $table->text('close_note')->nullable()->after('reminder');
            $table->integer('outcome_type')->nullable()->after('close_note');
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
    }
}
