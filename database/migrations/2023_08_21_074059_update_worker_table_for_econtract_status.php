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
            // e-contract worker status column
            $table->enum('econtract_status',['On-Bench', 'Assigned', 'Counselling', 'Repatriated', 'e-Run'])->default('On-Bench')->index()->after('total_management_status');
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
