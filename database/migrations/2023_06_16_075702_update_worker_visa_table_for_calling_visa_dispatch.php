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
            // Column for dispatch method
            $table->string('dispatch_method',255)->nullable();
            // Column for dispatch consignment number
            $table->string('dispatch_consignment_number',255)->nullable()->after('dispatch_method');
            // Column for dispatch acknowledgement number
            $table->string('dispatch_acknowledgement_number',255)->nullable()->after('dispatch_consignment_number');
            // Column for dispatch submiited on
            $table->date('dispatch_submitted_on')->nullable()->after('dispatch_acknowledgement_number');
            // Column for dispatch status
            $table->enum('dispatch_status',['Pending', 'Processed'])->default('Pending')->index()->after('dispatch_submitted_on');
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
