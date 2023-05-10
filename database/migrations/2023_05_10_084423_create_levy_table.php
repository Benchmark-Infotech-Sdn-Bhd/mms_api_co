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
        Schema::create('levy', function (Blueprint $table) {
            // Levy Id column
            $table->id();
            // Direct Recruitment Application Id column
            $table->unsignedBigInteger('application_id');
            // Foreign key from Direct Recruitment Application table
            $table->foreign('application_id')->references('id')->on('directrecruitment_applications')->onDelete('cascade');
            // item name column
            $table->string('item', 255);
            // payment date column
            $table->date('payment_date')->nullable();
            // payment amount column
            $table->float('payment_amount')->default(0.0);
            // approved quota column
            $table->integer('approved_quota')->default(0);
            // levy status column
            $table->enum('status', ['Pending', 'Paid'])->default('Pending');
            // ksm reference number column
            $table->string('ksm_reference_number', 255);
            // payment reference number column
            $table->string('payment_reference_number', 255);
            // approval number column
            $table->string('approval_number', 255);
            // new ksm reference number column
            $table->string('new_ksm_reference_number', 255);
            // remarks column
            $table->text('remarks')->nullable();
            // Column for user id who created the Levy 
            $table->integer('created_by')->default(0);
            // Column for user id who modified the Levy
            $table->integer('modified_by')->default(0);
            // Levy created time and modified time columns 
            $table->timestamps();
            // for softdelete
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('levy');
    }
};
