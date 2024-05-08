<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class InquiryQuestionOptions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {        
        Schema::create('inquiry_question_options', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('inquiry_question_id');
            $table->foreign('inquiry_question_id')->references('id')->on('inquiry_questions')->onDelete('cascade');
            $table->string('option',500);
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
