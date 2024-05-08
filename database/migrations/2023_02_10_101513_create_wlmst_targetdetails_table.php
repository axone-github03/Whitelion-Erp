<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWlmstTargetdetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wlmst_targetdetail', function (Blueprint $table) {
            $table->id();
			$table->integer('target_id')->default(0);
			$table->integer('month_number')->default(0);
			$table->string('month_name')->default('');
			$table->decimal('target_amount', 16,2)->default(00.00);
			$table->decimal('achiev_amount',16,2)->default(00.00);
			$table->decimal('freez_amount',16,2)->default(00.00);
			$table->date('freez_date')->nullable();
			$table->integer('freezby')->default(0);
			$table->string('freezip', 20)->default('');
			$table->integer('isfreez')->default(0)->comment('0 = not freez OR 1 = freez');
            
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
        Schema::dropIfExists('wlmst_targetdetail');
    }
}
