<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeadStatusUpdatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lead_status_updates', function (Blueprint $table) {
            $table->id();
            $table->string('lead_id', 100);
            $table->integer('old_status')->default(0);
            $table->integer('new_status')->default(0);
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
        Schema::dropIfExists('lead_status_updates');
    }
}
