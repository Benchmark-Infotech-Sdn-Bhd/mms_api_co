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
        Schema::table('worker_arrival', function (Blueprint $table) {
            // Column for arrived date
            $table->date('arrived_date')->nullable()->after('arrival_status');
            // Column for entry visa valid until date
            $table->date('entry_visa_valid_until')->nullable()->after('arrived_date');
            // Column for JTK Report submitted on
            $table->date('jtk_submitted_on')->nullable()->after('entry_visa_valid_until');
            // Column for new arrival date if postponed
            $table->date('new_arrival_date')->nullable()->after('jtk_submitted_on');
            // Coulmn for flight number
            $table->string('flight_number')->nullable()->after('new_arrival_date');
            // Column for arrival time
            $table->time('arrival_time')->nullable()->after('flight_number');
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
