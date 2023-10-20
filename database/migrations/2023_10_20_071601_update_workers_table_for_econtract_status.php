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
            if (DB::getDriverName() !== 'sqlite') {
                // econtract_status column
                \DB::statement("ALTER TABLE `workers` CHANGE `econtract_status` `econtract_status` ENUM('On-Bench', 'Assigned', 'Counselling', 'Repatriated', 'e-Run', 'Deceased') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'On-Bench';");
            }
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
