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
        Schema::table('levy', function (Blueprint $table) {
            $table->index(['new_ksm_reference_number']);
            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('levy', function (Blueprint $table) {
            $table->dropIndex(['new_ksm_reference_number']);
            $table->dropIndex(['status']);
        });
    }
};
