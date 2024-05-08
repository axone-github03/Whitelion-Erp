<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOtherNameColumnInWltrnQuotation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wltrn_quotation', function (Blueprint $table) {
            $table->string('other_name', 100)->nullable()->after('channelpartner_id');
            $table->string('other_mobile_no', 14)->nullable()->after('other_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('wltrn_quotation', function (Blueprint $table) {
            //
        });
    }
}
