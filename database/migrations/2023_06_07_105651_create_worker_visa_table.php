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
        Schema::create('worker_visa', function (Blueprint $table) {
            // Worker visa id column
            $table->id();
            // worker visa workerid column
            $table->bigInteger('worker_id')->unsigned()->nullable();
            // Foreign key from Worker table
            $table->foreign('worker_id')->references('id')->on('workers')->onDelete('cascade');
            // Column for KSM reference number column
            $table->string('ksm_reference_number', 255);
            // Column for Calling Visa reference number column
            $table->string('calling_visa_reference_number', 255)->nullable();
            // Column for calling Visa validity date column
            $table->date('calling_visa_valid_until')->nullable();
            // Column for entry Visa validity date column
            $table->date('entry_visa_valid_until')->nullable();
            // Column for work permit validity date column
            $table->date('work_permit_valid_until')->nullable();        
            // Column for user id who created the Worker
            $table->integer('created_by')->default(0);
            // Column for user id who modified the Worker
            $table->integer('modified_by')->default(0);
            // Worker created time and modified time columns 
            $table->timestamps();
            // for softdelete
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('worker_visa');
    }
};
