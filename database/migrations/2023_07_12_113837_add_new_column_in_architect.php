<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewColumnInArchitect extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('architect', function (Blueprint $table) {
            $table->tinyInteger('status')->default(2);
			$table->string('recording')->default('');
			$table->string('tele_note')->default('');
			$table->string('hod_note')->default('');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('architect', function (Blueprint $table) {
            //
        });
    }
}
