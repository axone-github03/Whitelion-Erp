<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class ValueTransferCreatedAtToJoiningDateInArchitect extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('architect', function (Blueprint $table) {
            DB::statement("UPDATE architect SET joining_date = created_at");
        });

        Schema::table('electrician', function (Blueprint $table) {
            DB::statement("UPDATE electrician SET joining_date = created_at");
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
            
        });
    }
}
