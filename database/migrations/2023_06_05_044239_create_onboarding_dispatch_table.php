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
        Schema::create('onboarding_dispatch', function (Blueprint $table) {
            // Column Id
            $table->id();
            // onboarding attestation Id column
            $table->unsignedBigInteger('onboarding_attestation_id');
            // Foreign key from onboarding attestation table
            if (DB::getDriverName() !== 'sqlite') {
              $table->foreign('onboarding_attestation_id')
              ->references('id')->on('onboarding_attestation')->onDelete('cascade');
            }
            // Submission Date column
            $table->string('date')->nullable();
            // time column
            $table->string('time', 10)->nullable();
            // Reference number column
            $table->string('reference_number', 255)->nullable();
            // Employee Id column
            $table->unsignedBigInteger('employee_id');
            // Foreign key from employee table
            $table->foreign('employee_id')
              ->references('id')->on('employee')->onDelete('cascade');
            // from column
            $table->string('from', 255)->nullable();
            // Call Time column
            $table->string('calltime', 255)->nullable();
            // Area column
            $table->string('area', 255)->nullable();
            // Employer name column
            $table->string('employer_name', 255)->nullable();
            // Phone number column
            $table->string('phone_number', 255)->nullable();
            // Remarks column
            $table->text('remarks')->nullable();
            // Column for user id who created the onboarding dispatch
            $table->integer('created_by')->default(0);
            // Column for user id who modified the onboarding dispatch
            $table->integer('modified_by')->default(0);
            // Onboarding created time and modified time columns 
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
        Schema::dropIfExists('onboarding_dispatch');
    }
};
