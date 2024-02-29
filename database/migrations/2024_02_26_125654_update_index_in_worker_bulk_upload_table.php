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
        Schema::table('worker_bulk_upload', function (Blueprint $table) {
            $table->index(['company_id']);
            $table->index(['module_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('worker_bulk_upload', function (Blueprint $table) {
            $table->dropIndex(['company_id']);
            $table->dropIndex(['module_type']);
        });
    }
};
