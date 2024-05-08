<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserTaskActionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_task_action', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->unsignedBigInteger('assign_to');
            $table->foreign('assign_to')->references('id')->on('users')->onDelete('cascade');

            $table->string('task', 255)->nullable();
            $table->dateTime('due_date_time')->nullable();
            $table->tinyInteger('is_notification')->default(0);
            $table->string('description', 1500)->nullable();
            $table->tinyInteger('is_closed')->default(0);
            $table->dateTime('closed_date_time')->nullable();
            $table->dateTime('reminder', 4)->nullable();
            $table->string('close_note', 1500)->nullable();
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
        Schema::dropIfExists('user_task_action');
    }
}
