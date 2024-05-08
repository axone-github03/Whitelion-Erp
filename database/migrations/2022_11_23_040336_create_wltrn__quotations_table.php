<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWltrnQuotationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wltrn_quotation', function (Blueprint $table) {
            $table->id();
            $table->integer('quotationgroup_id')->default(0);
            $table->string('yy',3)->default(0);
            $table->string('mm',3)->default(0);
            $table->integer('quotationtype_id')->default(0);
            $table->integer('quotationno')->default(0);
            $table->string('quotation_no_str')->default(0);
            $table->integer('customer_id')->default(0);
            $table->string('customer_name',100)->nullable();
            $table->string('customer_contact_no',13)->default(0);

            $table->integer('architech_id')->default(0);
            $table->integer('electrician_id')->default(0);
            $table->integer('salesexecutive_id')->default(0);
            $table->integer('channelpartner_id')->default(0);

            $table->integer('inquiry_id')->default(0);
            $table->string('site_name',255)->nullable();
            $table->string('siteaddress',255)->nullable();
            $table->integer('siteState_id')->default(0);
            $table->string('additional_remark',255)->nullable();
            $table->date('quotationdate')->nullable();
            $table->string('quotationsource',10)->nullable();
            $table->string('refrence_name',100)->nullable();
            $table->integer('refrencequotation_id')->default(0);
            $table->integer('totalitem')->default(0);
            $table->integer('totalqty')->default(0);
            $table->decimal('totalamount',10,2)->default(0);
            $table->decimal('addamount',10,2)->default(0);
            $table->decimal('lessamount',10,2)->default(0);
            $table->decimal('taxableamount',10,2)->default(0);
            $table->decimal('igst_amount',10,2)->default(0);
            $table->decimal('cgst_amount',10,2)->default(0);
            $table->decimal('sgst_amount',10,2)->default(0);
            $table->decimal('net_amount',10,2)->default(0);
            $table->string('status',50)->default(0);
            $table->string('email_count',50)->default(0);
            $table->string('print_count',50)->default(0);
            $table->integer('copyfromquotation_id')->default(0);
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
        Schema::dropIfExists('wltrn__quotations');
    }
}
