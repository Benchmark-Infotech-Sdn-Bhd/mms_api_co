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
        Schema::create('direct_recruitment_calling_visa', function (Blueprint $table) {
            // Column for calling visa Id
            $table->id();
            // Direct Recruitment Application Id column
            $table->unsignedBigInteger('application_id');
            // Foreign key from Direct Recruitment Application table
            $table->foreign('application_id')
              ->references('id')->on('directrecruitment_applications')->onDelete('cascade');
            // Country Id column
            $table->unsignedBigInteger('onboarding_country_id');
            // Foreign key from directrecruitment onboarding countries table
            $table->foreign('onboarding_country_id')
              ->references('id')->on('directrecruitment_onboarding_countries')->onDelete('cascade');
            // Direct Recruitment Agent Id column
            $table->unsignedBigInteger('agent_id');
            // Foreign key from Onboarding Agent table
            $table->foreign('agent_id')
              ->references('id')->on('directrecruitment_onboarding_agent')->onDelete('cascade');
            // Column for worker Id 
            $table->unsignedBigInteger('worker_id');
            // Foreign key from Workers table
            // $table->foreign('worker_id')
            //   ->references('id')->on('workers')->onDelete('cascade');
            // Calling visa status id column
            $table->unsignedBigInteger('calling_visa_status_id');
            // Foreign key from Onboarding calling visa status table
            $table->foreign('calling_visa_status_id')
              ->references('id')->on('direct_recruitment_calling_visa_status')->onDelete('cascade');
            // Calling visa reference number column
            $table->string('calling_visa_reference_number', 255);
            // Column for calling visa submitted time
            $table->date('submitted_on')->nullable();
            // Column for calling visa status
            $table->enum('status',['Pending', 'Processed'])->default('Pending');
            // Column for user id who created the calling visa
            $table->integer('created_by')->default(0);
            // Column for user id who modified the calling visa 
            $table->integer('modified_by')->default(0);
            // calling visa created time and modified time columns
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
        Schema::dropIfExists('direct_recruitment_calling_visa');
    }
};
