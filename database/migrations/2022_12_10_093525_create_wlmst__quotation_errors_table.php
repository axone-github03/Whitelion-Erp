<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWlmstQuotationErrorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wlmst_quotation_errors', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('quot_id');
            $table->bigInteger('quotgroup_id');
            $table->bigInteger('quotitemdetail_id');
            $table->text('srno')->nullable();
            $table->text('floorno',3)->nullable();
            $table->text('roomno',3)->nullable();

            $table->integer('old_company_id')->default(0);
            $table->integer('old_itemgroup_id')->default(0);
            $table->integer('old_itemsubgroup_id')->default(0);
            $table->integer('old_itemcategory_id')->default(0);
            $table->integer('old_item_id')->default(0);
            $table->string('old_itemcode',40)->nullable();
            $table->integer('old_item_price_id')->default(0);

            $table->integer('new_company_id')->default(0);
            $table->integer('new_itemgroup_id')->default(0);
            $table->integer('new_itemsubgroup_id')->default(0);
            $table->integer('new_itemcategory_id')->default(0);
            $table->integer('new_item_id')->default(0);
            $table->string('new_itemcode',40)->nullable();
            $table->integer('new_item_price_id')->default(0);
            
            $table->text('description')->nullable();
            $table->string('status',10)->nullable();

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
        Schema::dropIfExists('wlmst_quotation_errors');
    }
}
