<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeadQuestionAnswer extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lead_question_answer', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lead_question_id');
			$table->foreign('lead_question_id')->references('id')->on('lead_question')->onDelete('cascade');
			$table->tinyInteger('question_type')->default(0);
			$table->unsignedBigInteger('lead_id');
			$table->foreign('lead_id')->references('id')->on('leads')->onDelete('cascade');
			$table->string('reference_type')->nullable();
            $table->integer('reference_id')->default(0);
			$table->string('answer', 2000);
            $table->dateTime('created_at')->nullable();
			$table->integer('entryby')->default(0);
			$table->string('entryip', 20)->default('');
            $table->dateTime('updated_at')->nullable();
            $table->integer('updateby')->default(0);
            $table->string('updateip', 20)->default(''); 
            $table->string('source')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lead_question_answer');
    }
}
