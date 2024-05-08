<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeadAccountContactsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lead_account_contacts', function (Blueprint $table) {
            $table->id();
            $table->string('first_name', 50)->nullable();
            $table->string('last_name', 50)->nullable();
            $table->string('full_name', 255)->nullable();
            $table->string('email', 50)->nullable();
            $table->string('phone_number', 50)->nullable();
            $table->string('alt_phone_number', 50)->nullable();
            $table->string('lead_account_ids', 255)->nullable();
            $table->string('lead_contact_id', 255)->nullable();
            $table->string('lead_contact_tag_id', 255)->nullable();
            $table->tinyInteger('status')->default(1);
            $table->string('remark', 255)->nullable();
            $table->integer('entryby')->default(0);
            $table->string('entryip', 20)->nullable();
            $table->integer('updateby')->default(0);
            $table->string('updateip', 20)->nullable();
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
        Schema::dropIfExists('lead_account_contacts');
    }
}
