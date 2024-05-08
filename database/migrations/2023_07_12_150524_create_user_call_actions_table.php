<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserCallActionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_call_action', function (Blueprint $table) {
            $table->id();
            
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->unsignedBigInteger('type_id');
            $table->foreign('type_id')->references('id')->on('crm_setting_call_type')->onDelete('cascade');

            $table->string('contact_person', 255)->nullable();
            $table->string('purpose', 255)->nullable();
            $table->string('description', 1500)->nullable();
            $table->tinyInteger('is_notification')->default(0);
            $table->dateTime('call_schedule', 4)->nullable();
            $table->dateTime('reminder', 4)->nullable();
            $table->tinyInteger('is_closed')->default(0);
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
        Schema::dropIfExists('user_call_action');
    }
}
