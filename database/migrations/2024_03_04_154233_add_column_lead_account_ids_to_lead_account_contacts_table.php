<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnLeadAccountIdsToLeadAccountContactsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lead_account_contacts', function (Blueprint $table) {
            $table->text('lead_account_ids')->after('alt_phone_number');
            $table->text('lead_contact_id')->after('lead_account_ids');
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
