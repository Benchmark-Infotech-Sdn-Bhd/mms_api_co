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
        Schema::table('onboarding_dispatch', function (Blueprint $table) {
            $table->index(['reference_number']);
            $table->index(['dispatch_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('onboarding_dispatch', function (Blueprint $table) {
            $table->dropIndex(['reference_number']);
            $table->dropIndex(['dispatch_status']);
        });
    }
};
