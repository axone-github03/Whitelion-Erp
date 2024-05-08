<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWlmstItemPricesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wlmst_item_prices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->default(0);
            $table->foreign('company_id')->references('id')->on('wlmst_companies')->onDelete('cascade');
            $table->unsignedBigInteger('itemgroup_id')->default(0);
            $table->foreign('itemgroup_id')->references('id')->on('wlmst_item_groups')->onDelete('cascade');
            $table->unsignedBigInteger('itemsubgroup_id')->default(0);
            $table->foreign('itemsubgroup_id')->references('id')->on('wlmst_item_subgroups')->onDelete('cascade');
            $table->unsignedBigInteger('item_id')->default(0);
            $table->foreign('item_id')->references('id')->on('wlmst_items')->onDelete('cascade');
            $table->string('code')->default('');
            $table->decimal('mrp', 10, 2)->default('00.00');
            $table->decimal('discount', 10, 2)->default('00.00');
            $table->date('effectivedate')->nullable();
            $table->tinyInteger('isactive')->default(1);
            $table->string('remark')->default('');
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
        Schema::dropIfExists('wlmst_item_prices');
    }
}
