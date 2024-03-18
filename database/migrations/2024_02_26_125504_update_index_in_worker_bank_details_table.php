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
        Schema::table('worker_bank_details', function (Blueprint $table) {
            $table->index(['bank_name']);
            $table->index(['account_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('worker_bank_details', function (Blueprint $table) {
            $table->dropIndex(['bank_name']);
            $table->dropIndex(['account_number']);
        });
    }
};
