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
            // Column for PLKS status
            $table->enum('plks_status', ['Pending', 'Approved'])->default('Pending')->after('special_pass_valid_until');
            // Column for PLKS expiry date
            $table->date('plks_expiry_date')->nullable()->after('plks_status');
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
