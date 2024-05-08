<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserContactsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_contact', function (Blueprint $table) {
            $table->id();
            
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->integer('contact_tag_id')->default(0);
            $table->string('first_name', 255)->nullable();
            $table->string('last_name', 255)->nullable();
            $table->bigInteger('phone_number')->default(0);
            $table->bigInteger('alernate_phone_number')->default(0);
            $table->string('email', 255)->nullable();
            $table->bigInteger('type')->default(0);
            $table->string('type_detail', 255)->nullable();
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
        Schema::dropIfExists('user_contact');
    }
}
