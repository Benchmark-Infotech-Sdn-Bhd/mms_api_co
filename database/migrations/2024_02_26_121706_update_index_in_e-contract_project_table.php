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
        Schema::table('e-contract_project', function (Blueprint $table) {
            $table->index(['name']);
            $table->index(['state']);
            $table->index(['city']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('e-contract_project', function (Blueprint $table) {
            $table->dropIndex(['name']);
            $table->dropIndex(['state']);
            $table->dropIndex(['city']);
        });
    }
};
