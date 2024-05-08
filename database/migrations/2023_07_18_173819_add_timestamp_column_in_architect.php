<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTimestampColumnInArchitect extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('architect', function (Blueprint $table) {
            $table->integer('entryby')->default(0)->after('joining_date');
            $table->string('entryip', 20)->nullable()->after('entryby');
            $table->integer('updateby')->default(0)->after('created_at');
            $table->string('updateip', 20)->nullable()->after('updateby');
        });

        Schema::table('electrician', function (Blueprint $table) {
            $table->integer('entryby')->default(0)->after('joining_date');
            $table->string('entryip', 20)->nullable()->after('entryby');
            $table->integer('updateby')->default(0)->after('created_at');
            $table->string('updateip', 20)->nullable()->after('updateby');
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
