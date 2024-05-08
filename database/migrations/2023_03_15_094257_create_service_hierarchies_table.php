<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServiceHierarchiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_hierarchies', function (Blueprint $table) {
			$table->id();
			$table->string('name')->default('');
			$table->string('code')->default('');
			$table->bigInteger('parent_id')->default(0);
			$table->tinyInteger('status')->default(1);
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
        Schema::dropIfExists('service_hierarchies');
    }
}
