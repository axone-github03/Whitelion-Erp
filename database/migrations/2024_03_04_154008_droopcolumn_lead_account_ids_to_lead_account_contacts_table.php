<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DroopcolumnLeadAccountIdsToLeadAccountContactsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lead_account_contacts', function (Blueprint $table) {
            $table->dropColumn('lead_account_ids');
            $table->dropColumn('lead_contact_id');
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
