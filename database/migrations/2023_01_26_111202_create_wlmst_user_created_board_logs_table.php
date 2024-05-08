<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWlmstUserCreatedBoardLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wlmst_user_created_board_logs', function (Blueprint $table) {
            $table->id();
			$table->unsignedBigInteger('user_id');
			$table->integer('quot_id')->default(0);
			$table->integer('quotgroup_id')->default(0);
			$table->integer('room_no')->default(0);
			$table->integer('board_no')->default(0);
			$table->text('description')->nullable();
			$table->string('source', 40)->nullable();
			$table->dateTime('created_at')->nullable();
			$table->integer('entryby')->default(0);
			$table->string('entryip', 20)->default('');
            $table->dateTime('updated_at')->nullable();
            $table->integer('updateby')->default(0);
            $table->string('updateip', 20)->default(''); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('wlmst_user_created_board_logs');
    }
}
