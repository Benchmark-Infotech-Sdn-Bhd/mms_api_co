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
        Schema::table('onboarding_attestation', function (Blueprint $table) {
            $table->index(['onboarding_agent_id']);
            $table->index(['ksm_reference_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('onboarding_attestation', function (Blueprint $table) {
            $table->dropIndex(['onboarding_agent_id']);
            $table->dropIndex(['ksm_reference_number']);
        });
    }
};
