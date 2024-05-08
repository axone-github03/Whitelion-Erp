<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeColumnTypeContactTagIdInLeadContacts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lead_contacts', function (Blueprint $table) {
            $table->dropForeign(['contact_tag_id']);
            $table->dropColumn('contact_tag_id');
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lead_contacts', function (Blueprint $table) {
            //
        });
    }
}
