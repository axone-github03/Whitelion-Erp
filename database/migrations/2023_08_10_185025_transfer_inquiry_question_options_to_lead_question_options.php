<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class TransferInquiryQuestionOptionsToLeadQuestionOptions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lead_question_options', function (Blueprint $table) {
            $data = DB::table('inquiry_question_options')->where('inquiry_question_id', 1069)->get();

            if ($data) {
                foreach ($data as $record) {
                    if ($record !== null) {
                        DB::table('lead_question_options')->insert([
                            'lead_question_id' => 1,
                            'option' => $record->option,
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
        Schema::table('lead_question_options', function (Blueprint $table) {
            //
        });
    }
}
