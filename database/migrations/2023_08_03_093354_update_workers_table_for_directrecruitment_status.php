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
            // direct recruitment worker status column
            $table->enum('directrecruitment_status',['Pending', 'Accepted', 'Rejected', 'Not Arrived', 'Arrived', 'FOMEMA Fit', 'Processed', 'Repatriated', 'Cancelled'])->default('Pending')->index()->after('plks_expiry_date');
            if (DB::getDriverName() !== 'sqlite') {
                // total management worker status column
                \DB::statement("ALTER TABLE `workers` CHANGE `worker_status` `total_management_status` ENUM('On-Bench','Assigned','Counselling', 'Repatriated', 'e-Run', 'Deceased') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'On-Bench';");
            }
            if(DB::getDriverName() == 'sqlite') {
                $table->renameColumn('worker_status', 'total_management_status');
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
