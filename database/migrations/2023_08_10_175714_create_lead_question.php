<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeadQuestion extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lead_question', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('status')->default(0);
			$table->tinyInteger('type')->default(0);
			$table->string('question', 500)->default('');
			$table->tinyInteger('is_static')->default(0);
			$table->tinyInteger('is_required')->default(0);
			$table->bigInteger('sequence')->default(0);
			$table->tinyInteger('is_depend_on_answer')->default(0);
			$table->bigInteger('depended_question_id')->default(0);
			$table->string('depended_question_answer', 100)->default('');
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
        Schema::dropIfExists('lead_question');
    }
}
