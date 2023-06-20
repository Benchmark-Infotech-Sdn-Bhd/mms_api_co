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
        Schema::table('worker_insurance_details', function (Blueprint $table) {
            // Column for Insurance Provider Id
            $table->unsignedBigInteger('insurance_provider_id')->nullable();
            // Foreign key from Vendors table
            $table->foreign('insurance_provider_id')
               ->references('id')->on('vendors')->onDelete('cascade');
            // colmun IG amount
            $table->float('ig_amount')->default(0.0);
            // colmun Hospitalization amount
            $table->float('hospitalization_amount')->default(0.0);
            // Column for submitted time
            $table->date('insurance_submitted_on')->nullable();
            // Column for Expiry Date
            $table->date('insurance_expiry_date')->nullable();
            // Column for status
            $table->enum('insurance_status',['Pending', 'Purchased'])->default('Pending')->index();
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
