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
        Schema::create('distribution_project', function (Blueprint $table) {
            $table->id();
            $table->integer('distribution_id')->unsigned();
            $table->integer('project_id')->unsigned();
            $table->integer('percent');
            $table->decimal('amount');
            $table->timestamps();
        });

        // Schema::table('distributions', function (Blueprint $table) {
        //     $table->json('balances')->after('user_id')->nullable();
        // });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('distribution_project');

        // Schema::table('distributions', function (Blueprint $table) {
        //     $table->dropColumn('balances');
        // });
    }
};