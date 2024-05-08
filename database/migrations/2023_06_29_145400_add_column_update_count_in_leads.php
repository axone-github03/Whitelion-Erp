<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnUpdateCountInLeads extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->date('quotation_date')->nullable()->after('quotation');
            $table->integer('update_count')->default(0)->after('inquiry_id');
            $table->string('entryip', 20)->nullable()->after('created_by');
            $table->string('entry_source')->nullable()->after('created_by');
            $table->string('updateip', 20)->nullable()->after('updated_by');
            $table->string('update_source')->nullable()->after('updated_by');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('leads', function (Blueprint $table) {
            //
        });
    }
}
