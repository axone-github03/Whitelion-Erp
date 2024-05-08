<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Leads extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //

        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email', 255);
            $table->string('phone_number', 25);
            $table->tinyInteger('status')->default(0);
            $table->bigInteger('sub_status')->default(0);

            $table->string('house_no')->default('');
            $table->string('addressline1')->default('');
            $table->string('addressline2')->default('');
            $table->string('area')->default('');
            $table->string('pincode', 20)->default('');
            $table->bigInteger('city_id')->default(0);

            $table->string('meeting_house_no')->default('');
            $table->string('meeting_addressline1')->default('');
            $table->string('meeting_addressline2')->default('');
            $table->string('meeting_area')->default('');
            $table->string('meeting_pincode', 20)->default('');
            $table->bigInteger('meeting_city_id')->default(0);



            $table->bigInteger('site_stage')->default(0);
            $table->bigInteger('site_type')->default(0);
            $table->bigInteger('bhk')->default(0);
            $table->bigInteger('sq_foot')->default(0);
            $table->bigInteger('want_to_cover')->default(0);
            $table->string('source_type')->default('');
            $table->string('source')->default('');
            $table->bigInteger('budget')->default(0);
            $table->dateTime('closing_date_time')->nullable();
            $table->bigInteger('competitor')->default(0);

            $table->unsignedBigInteger('created_by');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('updated_by');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('cascade');

            $table->unsignedBigInteger('assigned_to');
            $table->foreign('assigned_to')->references('id')->on('users')->onDelete('cascade');


            $table->bigInteger('architect')->default(0);
            $table->bigInteger('electrician')->default(0);
            $table->tinyInteger('is_deal')->default(0);
            $table->bigInteger('user_id')->default(0);
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
