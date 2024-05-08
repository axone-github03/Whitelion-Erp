<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWlmstFinancialyearsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wlmst_financialyear', function (Blueprint $table) {
            $table->id();
			$table->string('name')->default('');
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
        Schema::dropIfExists('wlmst_financialyear');
    }
}
