<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ExhibitionInquiry extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //

        Schema::create('exhibition_inquiry', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('exhibition_id');
            $table->foreign('exhibition_id')->references('id')->on('exhibition')->onDelete('cascade');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('type', 255)->default('');
            $table->string('phone_number', 255)->default('');
            $table->string('email', 255)->default('');
            $table->string('first_name', 255)->default('');
            $table->string('last_name', 255)->default('');

            $table->unsignedBigInteger('city_id');
            $table->foreign('city_id')->references('id')->on('city_list')->onDelete('cascade');
            $table->string('address_line1', 255)->default('');
            $table->string('address_line2', 255)->default('');
            $table->string('plan_type', 255)->default('');
            $table->string('stage_of_site', 255)->default('');
            $table->string('source', 255)->default('');
            $table->string('remark', 255)->default('');
            $table->string('firm_name', 255)->default('');
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
