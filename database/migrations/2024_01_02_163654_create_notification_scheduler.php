<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationScheduler extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notification_scheduler', function (Blueprint $table) {
            $table->id();
            $table->string('from_mail')->nullable();
            $table->string('from_name')->nullable();
            $table->string('to_email')->nullable();
            $table->string('to_name')->nullable();
            $table->string('bcc_mail')->nullable();
            $table->string('cc_mail')->nullable();
            $table->text('subject')->nullable();
            $table->integer('transaction_id')->default(0);
            $table->string('transaction_name')->nullable();
            $table->text('transaction_type')->nullable();
            $table->text('transaction_detail')->nullable();
            $table->text('attachment')->nullable();
            $table->tinyInteger('status')->default(0);
            $table->text('remark')->nullable();
            $table->string('source')->nullable();
            $table->dateTime('created_at')->nullable();
			$table->integer('entryby')->default(0);
			$table->string('entryip', 20)->default('');
            $table->dateTime('updated_at')->nullable();
            $table->integer('updateby')->default(0);
            $table->string('updateip', 20)->default(''); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notification_scheduler');
    }
}
