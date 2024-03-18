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
        Schema::table('worker_visa', function (Blueprint $table) {
            $table->index(['ksm_reference_number']);
            $table->index(['calling_visa_reference_number']);
            $table->index(['approval_status']);
            $table->index(['generated_status']);
            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('worker_visa', function (Blueprint $table) {
            $table->dropIndex(['ksm_reference_number']);
            $table->dropIndex(['calling_visa_reference_number']);
            $table->dropIndex(['approval_status']);
            $table->dropIndex(['generated_status']);
            $table->dropIndex(['status']);
        });
    }
};
