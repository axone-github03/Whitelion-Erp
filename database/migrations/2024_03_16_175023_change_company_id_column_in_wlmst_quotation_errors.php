<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeCompanyIdColumnInWlmstQuotationErrors extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wlmst_quotation_errors', function (Blueprint $table) {
            $table->dropColumn('new_company_id');
            $table->dropColumn('new_itemgroup_id');
            $table->dropColumn('new_itemsubgroup_id');
            $table->string("new_company_id",50)->nullable()->after('new_range')->change();
            $table->string("new_itemgroup_id",50)->nullable()->after('new_company_id')->change();
            $table->string("new_itemsubgroup_id",50)->nullable()->after('new_itemgroup_id')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('wlmst_quotation_errors', function (Blueprint $table) {
            //
        });
    }
}
