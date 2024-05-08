<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddClaimDateTimeColumnInLeadFiles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lead_files', function (Blueprint $table) {
            $table->dateTime('claimed_date_time')->nullable();
            $table->dateTime('hod_approved_at')->nullable();
        });
        Schema::table('lead_timeline', function (Blueprint $table) {
            $table->string('reference_type', 100)->nullable();
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
    }
}
