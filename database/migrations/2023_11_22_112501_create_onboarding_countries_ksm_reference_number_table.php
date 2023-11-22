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
        Schema::create('onboarding_countries_ksm_reference_number', function (Blueprint $table) {
            // Id column
            $table->id();
            // Direct Recruitment Application Id column
            $table->unsignedBigInteger('application_id');
            // Foreign key from Direct Recruitment Application table
            $table->foreign('application_id')
              ->references('id')->on('directrecruitment_applications')->onDelete('cascade');
            // Country Id column
            $table->unsignedBigInteger('onboarding_country_id');
            // Foreign key from Contries table
            $table->foreign('onboarding_country_id', 'onboarding_country_id_foreign')->nullable()
              ->references('id')->on('directrecruitment_onboarding_countries')->onDelete('cascade');
            // $table->foreign('onboarding_country_id')
            //   ->references('id')->on('directrecruitment_onboarding_countries')->onDelete('cascade');
            // Column for ksm reference number
            $table->string('ksm_reference_number')->nullable();
            // KSM Reference number Valid Until column
            $table->date('valid_until');
            // Quota column
            $table->integer('quota')->default(0);
            // Utilised Quota column
            $table->integer('utilised_quota')->default(0);
            // Status column
            $table->tinyInteger('status')->default(1)->unsigned();
            // Column for user id who created the Direct recruitment onboarding countries ksm reference number
            $table->integer('created_by')->default(0);
            // Column for user id who modified the Direct recruitment onboarding countries ksm reference number
            $table->integer('modified_by')->default(0);
            // Onboarding countries ksm reference number created time and modified time columns 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('onboarding_countries_ksm_reference_number');
    }
};
