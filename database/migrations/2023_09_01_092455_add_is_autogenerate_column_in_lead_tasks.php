<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsAutogenerateColumnInLeadTasks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lead_tasks', function (Blueprint $table) {
            $table->tinyInteger('is_autogenerate')->default(0)->after('outcome_type');
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
