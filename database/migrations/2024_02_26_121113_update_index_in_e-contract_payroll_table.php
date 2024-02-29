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
        Schema::table('e-contract_payroll', function (Blueprint $table) {
            $table->index(['month']);
            $table->index(['year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('e-contract_payroll', function (Blueprint $table) {
            $table->dropIndex(['month']);
            $table->dropIndex(['year']);
        });
    }
};
