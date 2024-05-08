<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class InquiryQuestionAnswer extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create('inquiry_question_answer', function (Blueprint $table) {
			$table->id();

			$table->unsignedBigInteger('inquiry_question_id');
			$table->foreign('inquiry_question_id')->references('id')->on('inquiry_questions')->onDelete('cascade');
			$table->tinyInteger('question_type')->default(0);
			$table->unsignedBigInteger('inquiry_id');
			$table->foreign('inquiry_id')->references('id')->on('inquiry')->onDelete('cascade');
			$table->unsignedBigInteger('user_id');
			$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
			$table->string('answer', 2000);
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		//
	}
}
