<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeadAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lead_account', function (Blueprint $table) {
            $table->id();
            $table->string('first_name', 50)->nullable();
            $table->string('last_name', 50)->nullable();
            $table->string('full_name', 255)->nullable();
            $table->string('email', 50)->nullable();
            $table->string('phone_number', 50)->nullable();
            $table->string('address_line_1', 255)->nullable();
            $table->string('address_line_2', 255)->nullable();
            $table->string('pincode', 255)->nullable();
            $table->string('area', 255)->nullable();
            $table->bigInteger('country_id')->default(0);
            $table->bigInteger('state_id')->default(0);
            $table->bigInteger('city_id')->default(0);
            $table->string('lead_ids', 255)->nullable();
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
        Schema::dropIfExists('lead_accounts');
    }
}
