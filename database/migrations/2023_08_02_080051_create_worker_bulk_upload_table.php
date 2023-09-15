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
        Schema::create('worker_bulk_upload', function (Blueprint $table) {
            // Column for Id 
            $table->id();
            // Country Id column
            $table->unsignedBigInteger('onboarding_country_id');
            // Foreign key from directrecruitment onboarding countries table
            $table->foreign('onboarding_country_id')
              ->references('id')->on('directrecruitment_onboarding_countries')->onDelete('cascade');
            // Direct Recruitment Agent Id column
            $table->unsignedBigInteger('agent_id');
            // Foreign key from Agent table
            $table->foreign('agent_id')
              ->references('id')->on('agent')->onDelete('cascade');
            // Direct Recruitment Application Id column
            $table->unsignedBigInteger('application_id');
            // Foreign key from Direct Recruitment Application table
            $table->foreign('application_id')
              ->references('id')->on('directrecruitment_applications')->onDelete('cascade');
            // Column for Bulk upload Id 
            $table->string('name', 255);
            // Column for Bulk upload Type 
            $table->string('type', 255);
            // Column for Total Records
            $table->integer('total_records')->unsigned()->default(0);
            // Column for Total Success
            $table->integer('total_success')->unsigned()->default(0);
            // Column for Total Failure
            $table->integer('total_failure')->unsigned()->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('worker_bulk_upload');
    }
};
