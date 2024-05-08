<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeadAdvanceFilter extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lead_advance_filter', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->default(0);
            $table->integer('is_deal')->default(0);
            $table->string('name')->nullable();
            $table->integer('is_public')->default(0);
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
        Schema::dropIfExists('lead_advance_filter');
    }
}
