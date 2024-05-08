<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserMeetingActionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_meeting_action', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->unsignedBigInteger('title_id');
            $table->foreign('title_id')->references('id')->on('crm_setting_meeting_title')->onDelete('cascade');

            $table->unsignedBigInteger('type_id');
            $table->foreign('type_id')->references('id')->on('crm_setting_call_type')->onDelete('cascade');

            $table->string('location', 255)->nullable();
            $table->dateTime('meeting_date_time')->nullable();
            $table->string('description', 1500)->nullable();

            $table->tinyInteger('is_closed')->default(0);
            $table->tinyInteger('is_notification')->default(0);
            $table->dateTime('reminder', 4)->nullable();
            $table->string('close_note', 1500)->nullable();
            $table->dateTime('closed_date_time')->nullable();
            $table->integer('outcome_type')->default(0);
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
        Schema::dropIfExists('user_meeting_action');
    }
}
