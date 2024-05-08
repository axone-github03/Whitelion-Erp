<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class LeadTasks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //

        Schema::create('lead_tasks', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('lead_id');
            $table->foreign('lead_id')->references('id')->on('leads')->onDelete('cascade');

            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->unsignedBigInteger('assign_to');
            $table->foreign('assign_to')->references('id')->on('users')->onDelete('cascade');


            $table->string('task', 255);
            $table->dateTime('due_date_time')->nullable();
            $table->tinyInteger('is_notification')->default(0);
            $table->string('description', 1500);
            $table->dateTime('reminder')->nullable();
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
