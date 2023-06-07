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
        Schema::create('onboarding_attestation', function (Blueprint $table) {
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
            // Submission Date column
            $table->date('submission_date')->nullable();
            // Collection Date column
            $table->date('collection_date')->nullable();
            // Item name column
            $table->string('item_name', 255);
            // Status column
            $table->enum('status',['Pending', 'Submitted', 'Collected'])->default('Pending');
            // File Url column
            $table->text('file_url')->nullable();
            // Remarks column
            $table->text('remarks')->nullable();
            // Column for user id who created the onboarding Attestation
            $table->integer('created_by')->default(0);
            // Column for user id who modified the onboarding Attestation
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
        Schema::dropIfExists('onboarding_attestation');
    }
};
