<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWlmstProductWarrantiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wlmst_product_warranty', function (Blueprint $table) {
            $table->id();
			$table->string('fullname')->nullable();
			$table->string('mobile')->default(0);
			$table->string('email')->nullable();
			$table->string('address_houseno')->nullable();
			$table->string('address_society')->nullable();
			$table->string('address_area')->nullable();
			$table->string('address_city')->nullable();
			$table->string('invoice_image')->nullable();
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
        Schema::dropIfExists('wlmst_product_warranty');
    }
}
