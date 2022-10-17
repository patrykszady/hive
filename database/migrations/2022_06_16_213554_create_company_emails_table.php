<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('company_emails', function (Blueprint $table) {
            $table->id();
            $table->integer('vendor_id');
            // $table->string('server');
            $table->string('email');
            // $table->string('password');
            // $table->string('mailbox');
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
        Schema::dropIfExists('company_emails');
    }
};
