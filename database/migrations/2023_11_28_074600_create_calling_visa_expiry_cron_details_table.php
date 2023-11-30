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
        Schema::create('calling_visa_expiry_cron_details', function (Blueprint $table) {
            // Id column
            $table->id();
            // Direct Recruitment Application Id column
            $table->unsignedBigInteger('application_id');
            // Onboarding Country Id column
            $table->unsignedBigInteger('onboarding_country_id');
            // Column for ksm reference number
            $table->string('ksm_reference_number')->nullable();
            // Quota column
            $table->integer('approved_quota')->default(0);
            // Initial Utilised Quota column
            $table->integer('initial_utilised_quota')->default(0);
            // Current Utilised Quota column
            $table->integer('current_utilised_quota')->default(0);
            // entry created and updated time
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calling_visa_expiry_cron_details');
    }
};
