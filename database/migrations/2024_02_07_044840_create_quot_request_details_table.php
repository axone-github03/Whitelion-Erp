<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuotRequestDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quot_request_detail', function (Blueprint $table) {
            $table->id();
            $table->integer('quot_req_id')->default(0);
            $table->integer('group_id')->default(0);
            $table->integer('quot_id')->default(0);
            $table->integer('quot_room_no')->default(0);
            $table->integer('quot_board_no')->default(0);
            $table->integer('assign_to')->default(0);
            $table->integer('status')->default(0);
            $table->text('remark')->nullable();
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
        Schema::dropIfExists('quot_request_detail');
    }
}
