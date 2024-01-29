<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTimesheetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('timesheets', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->integer('user_id')->unsigned();
            $table->integer('vendor_id')->unsigned()->nullable();
            $table->integer('project_id')->unsigned()->nullable();
            $table->integer('hours');
            $table->decimal('amount');
            $table->integer('paid_by')->nullable();
            $table->integer('check_id')->unsigned()->nullable();
            $table->integer('hourly');
            $table->string('invoice')->nullable();
            $table->string('note')->nullable();   
            $table->integer('created_by_user_id');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('timesheets');
    }
};
