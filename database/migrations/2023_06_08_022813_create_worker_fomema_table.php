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
        Schema::create('worker_fomema', function (Blueprint $table) {
            // Worker fomema id column
            $table->id();
            // worker fomema workerid column
            $table->bigInteger('worker_id')->unsigned()->nullable();
            // Foreign key from Worker table
            $table->foreign('worker_id')->references('id')->on('workers')->onDelete('cascade');            
            // Column for purchase date
            $table->date('purchase_date')->nullable();
            // Column for clinic name
            $table->string('clinic_name', 255)->nullable();
            // Column for doctor code
            $table->string('doctor_code', 255)->nullable();
            // Column for allocated xray
            $table->string('allocated_xray', 255)->nullable();
            // Column for xray code
            $table->string('xray_code', 255)->nullable();
            // Column for user id who created the Worker fomema 
            $table->integer('created_by')->default(0);
            // Column for user id who modified the Worker fomema
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
        Schema::dropIfExists('worker_fomema');
    }
};
