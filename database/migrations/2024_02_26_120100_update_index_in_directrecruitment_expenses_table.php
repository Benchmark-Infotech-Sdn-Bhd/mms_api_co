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
        Schema::table('directrecruitment_expenses', function (Blueprint $table) {
            $table->index(['title']);
            $table->index(['payment_reference_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('directrecruitment_expenses', function (Blueprint $table) {
            $table->dropIndex(['title']);
            $table->dropIndex(['payment_reference_number']);
        });
    }
};
