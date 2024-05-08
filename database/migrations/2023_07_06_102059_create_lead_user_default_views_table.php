<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeadUserDefaultViewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_default_views', function (Blueprint $table) {
            $table->id();
            $table->integer('lead_filterview_id')->default(0);
            $table->integer('user_id')->default(0);
            $table->integer('user_type')->default(0);
            $table->string('default_type')->nullable();
            $table->string('module')->nullable();
            $table->string('remark')->nullable();
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
        Schema::dropIfExists('user_default_views');
    }
}
