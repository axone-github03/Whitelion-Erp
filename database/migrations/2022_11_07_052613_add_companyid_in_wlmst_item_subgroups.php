<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCompanyidInWlmstItemSubgroups extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wlmst_item_subgroups', function (Blueprint $table) {
            $table->unsignedBigInteger('company_id')->default(0)->after('itemsubgroupname');
            $table->foreign('company_id')->references('id')->on('wlmst_companies')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('wlmst_item_subgroups', function (Blueprint $table) {
            //
        });
    }
}
