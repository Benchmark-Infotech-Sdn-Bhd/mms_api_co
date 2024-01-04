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
        Schema::table('worker_fomema', function (Blueprint $table) {
            // Column for fomema total charges
            $table->float('fomema_total_charge')->default(0.00)->after('xray_code');
            // Column for convenient fee
            $table->float('convenient_fee')->default(0.00)->after('fomema_total_charge');
            // Column for fomema status
            $table->enum('fomema_status', ['Pending', 'Fit', 'Unfit'])->default('Pending')->after('convenient_fee');
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
