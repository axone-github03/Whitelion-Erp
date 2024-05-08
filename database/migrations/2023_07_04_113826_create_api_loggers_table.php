<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApiLoggersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('api_logger', function (Blueprint $table) {
            $table->id();
            $table->string('url')->nullable();
            $table->string('method')->nullable();
            $table->text('body')->nullable();
            $table->text('header')->nullable();
            $table->string('ip')->nullable();
            $table->string('status_code')->nullable();
            $table->text('user_agent')->nullable();
            $table->text('remark')->nullable();
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
        Schema::dropIfExists('api_loggers');
    }
}
