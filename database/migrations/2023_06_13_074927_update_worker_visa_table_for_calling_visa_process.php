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
        Schema::table('worker_visa', function (Blueprint $table) {
            // Column for calling visa submitted time
            $table->date('submitted_on')->nullable()->after('calling_visa_reference_number');
            // Column for calling visa generated date
            $table->date('calling_visa_generated')->nullable()->after('submitted_on');
            // Column for calling visa status
            $table->enum('status',['Pending', 'Processed', 'Approved', 'Rejected'])->default('Pending')->after('calling_visa_valid_until');
            // Column for calling visa remarks
            $table->text('remarks')->nullable()->after('work_permit_valid_until');
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
