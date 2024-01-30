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
        Schema::create('vendor_docs', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->integer('vendor_id')->unsigned();
            $table->date('effective_date');
            $table->date('expiration_date');
            $table->string('number')->nullable();
            $table->integer('belongs_to_vendor_id');
            $table->string('doc_filename');
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
        Schema::dropIfExists('vendor_docs');
    }
};
