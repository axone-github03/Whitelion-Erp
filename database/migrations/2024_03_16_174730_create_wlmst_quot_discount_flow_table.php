<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWlmstQuotDiscountFlowTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wlmst_quot_discount_flow', function (Blueprint $table) {
            $table->id();
			$table->string('name')->nullable();
			$table->bigInteger('code')->default(0);
            $table->decimal('default_discount', 10, 2);
			$table->decimal('user_discount', 10, 2);
            $table->integer('status')->default(0);
            $table->text('remark')->nullable();
            $table->integer('entryby')->default(0);
            $table->string('entryip', 20)->nullable();
            $table->integer('updateby')->default(0);
            $table->string('updateip', 20)->default(''); 
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
        Schema::dropIfExists('wlmst_quot_discount_flow');
    }
}
