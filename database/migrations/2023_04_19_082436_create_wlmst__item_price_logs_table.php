<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWlmstItemPriceLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wlmst_item_price_logs', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('price_id')->default(0);
            $table->bigInteger('company_id')->default(0);
            $table->bigInteger('itemgroup_id')->default(0);
            $table->bigInteger('itemsubgroup_id')->default(0);
            $table->bigInteger('item_id')->default(0);
            $table->string('code')->default('');
            $table->decimal('mrp', 10, 2)->default('00.00');
            $table->decimal('discount', 10, 2)->default('00.00');
            $table->date('effectivedate')->nullable();
            $table->string('item_type',255)->nullable();
            $table->string('image',255)->nullable();
            $table->string('thumb_image',255)->nullable();
            $table->tinyInteger('isactive')->default(1);
            $table->string('remark')->default('');
            $table->dateTime('created_at')->nullable();
            $table->integer('entryby')->default(0);
            $table->string('entryip', 20)->default('');
            $table->dateTime('updated_at')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('wlmst_item_price_logs');
    }
}
