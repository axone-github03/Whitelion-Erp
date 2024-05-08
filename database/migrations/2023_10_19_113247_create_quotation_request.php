<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuotationRequest extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quotation_request', function (Blueprint $table) {
            $table->id();
            $table->integer('group_id')->default(0);
            $table->integer('quot_id')->default(0);
            $table->integer('quotgroup_id')->default(0);
            $table->integer('subgroup_id')->default(0);
            $table->decimal('discount')->default(0.00);
            $table->integer('deal_id')->default(0);
            $table->text('title')->nullable();
            $table->integer('assign_to')->default(0);
            $table->integer('status')->default(0);

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
        Schema::dropIfExists('quotation_request');
    }
}
