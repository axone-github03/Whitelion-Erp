<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeadAdvanceFilterItemLog extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lead_advance_filter_item_log', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->default(0);
            $table->integer('is_deal')->default(0);
            $table->integer('advance_filter_id')->default(0);
            $table->integer('clause_id')->default(0);
            $table->integer('column_id')->default(0);
            $table->integer('condition_id')->default(0);
            $table->string('value')->nullable();
            $table->string('log_type')->nullable();
            $table->timestamps();
            $table->integer('created_by')->default(0);
			$table->string('created_ip', 20)->default('');
            $table->integer('updated_by')->default(0);
            $table->string('updated_ip', 20)->default('');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lead_advance_filter_item_log');
    }
}
