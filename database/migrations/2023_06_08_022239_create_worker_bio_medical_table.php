<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('worker_bio_medical', function (Blueprint $table) {
            // Worker bio medical id column
            $table->id();
            // worker bio medical workerid column
            $table->bigInteger('worker_id')->unsigned()->nullable();
            // Foreign key from Worker table
            $table->foreign('worker_id')->references('id')->on('workers')->onDelete('cascade');
            // Column for bio medical reference number column
            $table->string('bio_medical_reference_number', 255);
            // Column for bio medical validity date column
            $table->date('bio_medical_valid_until');
            // Column for user id who created the Worker bio medical 
            $table->integer('created_by')->default(0);
            // Column for user id who modified the Worker bio medical 
            $table->integer('modified_by')->default(0);
            // vendor created time and modified time columns 
            $table->timestamps();
            // for soft delete
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('worker_bio_medical');
    }
};
