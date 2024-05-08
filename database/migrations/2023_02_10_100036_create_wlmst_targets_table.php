<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWlmstTargetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wlmst_target', function (Blueprint $table) {
            $table->id();
			$table->integer('employeee_id')->default(0);
			$table->integer('finyear_id')->default(0);
			$table->decimal('minachivement', 4,2)->default(00.00);
			$table->decimal('total_target',16,2)->default(00.00);
			$table->integer('distribute_type')->default(0)->comment('distributation_type 1 = Equal OR 2 = Incremental OR 3 = Incremental Per OR 4 = Incremental Manual');
            
			$table->string('source',50)->default('')->comment('Define Here Entry Source');
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
        Schema::dropIfExists('wlmst_target');
    }
}
