<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWlmstAppversionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wlmst_appversion', function (Blueprint $table) {
            $table->id();
            $table->string('source')->nullable(false);
            $table->string('version')->nullable(false);
            $table->tinyInteger('isactive')->default(1);
            $table->string('remark')->nullable();
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
        Schema::dropIfExists('wlmst_appversion');
    }
}
