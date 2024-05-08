<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSourceAndIpColumnInMarketingOrderItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('marketing_order_items', function (Blueprint $table) {
            $table->string('entryip', 20)->nullable()->after('sample_image');
            $table->string('source')->nullable()->after('updated_at');
        });

        Schema::table('marketing_orders', function (Blueprint $table) {
            $table->string('entryip', 20)->nullable()->after('remark');
            $table->string('source')->nullable()->after('updated_at');
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

        Schema::table('marketing_orders', function (Blueprint $table) {
            //
        });
    }
}
