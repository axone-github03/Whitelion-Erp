<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TransferInquiryQuestionsToLeadQuestion extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lead_question', function (Blueprint $table) {
            $data = DB::table('inquiry_questions')->where('id', 1069)->get();

            if ($data) {
                foreach ($data as $record) {
                    if ($record !== null) {
                        DB::table('lead_question')->insert([
                            'status' => 103,
                            'type' => $record->type,
                            'question' => $record->question,
                            'is_static' => $record->is_static,
                            'is_required' => $record->is_required,
                            'sequence' => $record->sequence,
                            'is_depend_on_answer' => $record->is_depend_on_answer,
                            'depended_question_id' => isset($record->depended_question_id) ? $record->depended_question_id : 0,
                            'depended_question_answer' => isset($record->depended_question_answer) ? $record->depended_question_answer : 0,
                            'source' => 'web'
                        ]);
                    }
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lead_question', function (Blueprint $table) {
            //
        });
    }
}