<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCustomProductColumnInMarketingOrderItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('marketing_order_items', function (Blueprint $table) {
            $table->integer('width')->default(0)->after('total_weight');
            $table->integer('height')->default(0)->after('width');
            $table->string('box_image')->nullable()->after('height');
            $table->string('sample_image')->nullable()->after('box_image');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('marketing_order_items', function (Blueprint $table) {
            //
        });
    }
}
