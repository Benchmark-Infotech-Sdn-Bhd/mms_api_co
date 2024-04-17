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
        Schema::table('company_renewal_notifications', function (Blueprint $table) {
            if (DB::getDriverName() !== 'sqlite') {
                \DB::statement("ALTER TABLE `company_renewal_notifications` CHANGE `renewal_frequency_cycle` `renewal_frequency_cycle` ENUM('None','Daily','Weekly','Monthly') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'None';");
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
