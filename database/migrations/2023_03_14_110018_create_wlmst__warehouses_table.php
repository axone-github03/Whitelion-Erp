<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWlmstWarehousesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wlmst_warehouses', function (Blueprint $table) {
            $table->id();
			$table->string('warehousename')->nullable();
			$table->string('shortname')->nullable();
            $table->string('address1')->nullable();
            $table->string('address2')->nullable();
			$table->integer('country')->default('0');
			$table->integer('state')->default('0');
			$table->integer('city')->default('0');
			$table->string('pincode')->nullable();
			$table->tinyInteger('isactive')->default(1);
			$table->text('remark')->nullable();
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
        Schema::dropIfExists('wlmst_warehouses');
    }
}
