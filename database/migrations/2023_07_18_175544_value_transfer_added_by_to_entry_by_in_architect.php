<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ValueTransferAddedByToEntryByInArchitect extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('architect', function (Blueprint $table) {
            DB::statement("UPDATE architect SET entryby = added_by");
        });

        Schema::table('electrician', function (Blueprint $table) {
            DB::statement("UPDATE electrician SET entryby = added_by");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('architect', function (Blueprint $table) {
            //
        });
        
        Schema::table('electrician', function (Blueprint $table) {
            //
        });
    }
}
