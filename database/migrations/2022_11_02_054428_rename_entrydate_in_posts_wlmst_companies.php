<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameEntrydateInPostsWlmstCompanies extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wlmst_companies', function (Blueprint $table) {
            $table->renameColumn('entrydate', 'created_at');
            $table->renameColumn('updatedate', 'updated_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('wlmst_companies', function (Blueprint $table) {
            //
        });
    }
}
