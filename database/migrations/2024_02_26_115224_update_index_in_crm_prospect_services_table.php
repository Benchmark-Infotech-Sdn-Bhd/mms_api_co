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
        Schema::table('crm_prospect_services', function (Blueprint $table) {
            $table->index(['contract_type']);
            $table->index(['status']);
            $table->index(['from_existing']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('crm_prospect_services', function (Blueprint $table) {
            $table->dropIndex(['contract_type']);
            $table->dropIndex(['status']);
            $table->dropIndex(['from_existing']);
        });
    }
};
