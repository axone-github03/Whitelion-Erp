<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReviewMastersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('review_master', function (Blueprint $table) {
            $table->id();
            $table->string('image')->nullable();
            $table->string('name');
            $table->integer('country_id')->default(0);
            $table->integer('state_id')->default(0);
            $table->integer('city_id')->default(0);
            $table->text('review')->nullable();
            $table->integer('is_fix')->default(0);
            $table->tinyInteger('status')->default(1);
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
        Schema::dropIfExists('review_master');
    }
}
