<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusColumnInLeadFiles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lead_files', function (Blueprint $table) {
            $table->integer('status')->default(0)->after('point');
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->integer('reward_status')->default(0)->after('total_point');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lead_files', function (Blueprint $table) {
            //
        });

        Schema::table('leads', function (Blueprint $table) {
            //
        });
    }
}
