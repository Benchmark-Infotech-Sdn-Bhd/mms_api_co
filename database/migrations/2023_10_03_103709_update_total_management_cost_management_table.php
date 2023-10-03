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
        Schema::table('total_management_cost_management', function (Blueprint $table) {
            // Invoice Number column
            $table->integer('is_payroll')->default(0);
            $table->integer('payroll_id')->default(0);
            $table->integer('month')->default(0);
            $table->integer('year')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
