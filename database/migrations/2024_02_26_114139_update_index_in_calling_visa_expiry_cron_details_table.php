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
        Schema::table('calling_visa_expiry_cron_details', function (Blueprint $table) {
            $table->index(['onboarding_country_id']);
            $table->index(['application_id']);
            $table->index(['ksm_reference_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('calling_visa_expiry_cron_details', function (Blueprint $table) {
            $table->dropIndex(['onboarding_country_id']);
            $table->dropIndex(['application_id']);
            $table->dropIndex(['ksm_reference_number']);
        });
    }
};
