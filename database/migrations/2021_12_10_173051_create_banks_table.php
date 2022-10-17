<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBanksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('banks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('plaid_access_token')->nullable();
            $table->string('plaid_item_id')->nullable();
            $table->integer('vendor_id')->unsigned();
            $table->string('plaid_ins_id');
            $table->json('plaid_options')->nullable();
            // $table->integer('distribution_id_credit_payment')->nullable();
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
        Schema::dropIfExists('banks');
    }
}
