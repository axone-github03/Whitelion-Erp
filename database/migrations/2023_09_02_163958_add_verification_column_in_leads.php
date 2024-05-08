<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVerificationColumnInLeads extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->integer('telesales_verification')->default(0)->after('tag');
            $table->integer('service_verification')->default(0)->after('telesales_verification');
            $table->integer('companyadmin_verification')->default(0)->after('service_verification');
            $table->integer('hod_verification')->default(0)->after('companyadmin_verification');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('leads', function (Blueprint $table) {
            //
        });
    }
}
