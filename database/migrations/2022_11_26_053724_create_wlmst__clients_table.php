<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWlmstClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wlmst_client', function (Blueprint $table) {
            $table->id();
            $table->string('name',150)->default('');
            $table->string('email',100)->default('');
            $table->string('mobile',15)->default('');
            $table->string('address',255)->default('');
            $table->tinyInteger('isactive')->default(1);
            $table->string('remark')->default('');
            $table->dateTime('entrydate')->nullable();
            $table->integer('entryby')->default(0);
            $table->string('entryip', 20)->default('');
            $table->dateTime('updatedate')->nullable();
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
        Schema::dropIfExists('wlmst__clients');
    }
}
