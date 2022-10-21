<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOnlineTransactionDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('online_transaction_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->string('tran_id')->nullable();
            $table->string('val_id')->nullable();
            $table->string('amount')->nullable();
            $table->string('card_type')->nullable();
            $table->string('store_amount')->nullable();
            $table->string('card_no')->nullable();
            $table->string('bank_tran_id')->nullable();
            $table->string('status')->nullable();
            $table->string('tran_date')->nullable();
            $table->string('error')->nullable();
            $table->string('currency')->nullable();
            $table->string('card_issuer')->nullable();
            $table->string('card_brand')->nullable();
            $table->string('card_sub_brand')->nullable();
            $table->string('card_issuer_country')->nullable();
            $table->string('card_issuer_country_code')->nullable();
            $table->string('store_id')->nullable();
            $table->string('currency_type')->nullable();
            $table->string('currency_amount')->nullable();
            $table->string('currency_rate')->nullable();
            $table->string('base_fair')->nullable();
            $table->string('value_a')->nullable();
            $table->string('value_b')->nullable();
            $table->string('value_c')->nullable();
            $table->string('value_d')->nullable();
            $table->string('subscription_id')->nullable();
            $table->string('risk_level')->nullable();
            $table->string('risk_title')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('online_transaction_details');
    }
}
