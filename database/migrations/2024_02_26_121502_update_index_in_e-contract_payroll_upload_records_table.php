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
        Schema::table('e-contract_payroll_upload_records', function (Blueprint $table) {
            $table->index(['success_flag']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('e-contract_payroll_upload_records', function (Blueprint $table) {
            $table->dropIndex(['success_flag']);
        });
    }
};
