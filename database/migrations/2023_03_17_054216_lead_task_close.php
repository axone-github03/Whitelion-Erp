<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class LeadTaskClose extends Migration
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
            $table->tinyInteger('is_closed')->default(0)->after('description');
        });

        Schema::table('lead_tasks', function (Blueprint $table) {
            $table->tinyInteger('is_closed')->default(0)->after('description');
        });

        Schema::table('lead_meetings', function (Blueprint $table) {
            $table->tinyInteger('is_closed')->default(0)->after('description');
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
