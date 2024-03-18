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
        Schema::table('workers', function (Blueprint $table) {
            $table->index(['name']);
            $table->index(['crm_prospect_id']);
            $table->index(['plks_status']);
            $table->index(['status']);
            $table->index(['module_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workers', function (Blueprint $table) {
            $table->dropIndex(['name']);
            $table->dropIndex(['crm_prospect_id']);
            $table->dropIndex(['plks_status']);
            $table->dropIndex(['status']);
            $table->dropIndex(['module_type']);
        });
    }
};
