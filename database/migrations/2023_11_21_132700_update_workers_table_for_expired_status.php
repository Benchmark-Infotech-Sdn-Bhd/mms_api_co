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
                // worker_status column
                \DB::statement("ALTER TABLE `workers` CHANGE `directrecruitment_status` `directrecruitment_status` ENUM('Pending','Accepted','Rejected','Not Arrived','Arrived','FOMEMA Fit','Processed','Repatriated','Cancelled','Expired') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pending';");
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
