<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class LeadContacts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //

        Schema::create('lead_contacts', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('lead_id');
            $table->foreign('lead_id')->references('id')->on('leads')->onDelete('cascade');

            $table->unsignedBigInteger('contact_tag_id');
            $table->foreign('contact_tag_id')->references('id')->on('crm_setting_contact_tag')->onDelete('cascade');

            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone_number', 25);
            $table->string('alernate_phone_number', 25);
            $table->string('email', 255);
            $table->timestamps();
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
