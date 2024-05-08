<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnTypeTypedetailInLeadAccountContacts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lead_account_contacts', function (Blueprint $table) {
            $table->bigInteger('type')->default(0)->after('email');
            $table->string('type_detail',255)->nullable()->after('type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lead_account_contacts', function (Blueprint $table) {
            //
        });
    }
}
