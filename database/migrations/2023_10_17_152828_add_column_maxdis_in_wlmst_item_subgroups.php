<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnMaxdisInWlmstItemSubgroups extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wlmst_item_subgroups', function (Blueprint $table) {
            $table->decimal('manager_maxdisc')->default(00.00)->after('maxdisc');
            $table->decimal('company_admin_maxdisc')->default(00.00)->after('manager_maxdisc');
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
