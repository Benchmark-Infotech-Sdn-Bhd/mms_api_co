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
        Schema::create('directrecruitment_onboarding_agent', function (Blueprint $table) {
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
            // Foreign key from Agent table
            $table->foreign('agent_id')
              ->references('id')->on('agent')->onDelete('cascade');
            // Quota column
            $table->integer('quota')->default(0);
            // Status column
            $table->tinyInteger('status')->default(1)->unsigned();
            // Column for user id who created the Direct recruitment onboarding Agent
            $table->integer('created_by')->default(0);
            // Column for user id who modified the Direct recruitment onboarding Agent
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
        Schema::dropIfExists('directrecruitment_onboarding_agent');
    }
};
