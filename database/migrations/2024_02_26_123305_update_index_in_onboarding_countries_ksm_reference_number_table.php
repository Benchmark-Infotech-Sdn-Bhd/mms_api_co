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
        Schema::table('onboarding_countries_ksm_reference_number', function (Blueprint $table) {
            $table->index(['ksm_reference_number'], 'ksm_reference_number_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('onboarding_countries_ksm_reference_number', function (Blueprint $table) {
            $table->dropIndex(['ksm_reference_number_index']);
        });
    }
};
