<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAdditonalColumnInLeadCalls extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lead_calls', function (Blueprint $table) {
            $table->string('architect_name', 100)->nullable();
            $table->string('electrician_name', 100)->nullable();
            $table->string('additional_info', 100)->nullable();
            $table->string('additional_info_text', 100)->nullable();
        });

        Schema::table('lead_tasks', function (Blueprint $table) {
            $table->string('architect_name', 100)->nullable()->after('is_autogenerate');
            $table->string('electrician_name', 100)->nullable()->after('architect_name');
            $table->string('additional_info', 100)->nullable()->after('electrician_name');
            $table->string('additional_info_text', 100)->nullable()->after('additional_info');
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

        Schema::table('lead_tasks', function (Blueprint $table) {
            //
        });
    }
}
