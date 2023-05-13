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
        Schema::create('application_interviews', function (Blueprint $table) {
            // application Interview Id column
            $table->id();
            // Direct Recruitment Application Checklist Id column
            $table->unsignedBigInteger('application_id');
            // Foreign key from Direct Recruitment Application table
            $table->foreign('application_id')
              ->references('id')->on('directrecruitment_applications')->onDelete('cascade');
            // KSM reference number column
            $table->string('ksm_reference_number', 255);
            // Schedule Date column
            $table->date('schedule_date');
            // Approved Quota column
            $table->integer('approved_quota');
            // Approval Date column
            $table->date('approval_date');
            // Status column
            $table->enum('status',['Scheduled', 'Completed', 'Approved'])->default('Scheduled');
            // Remarks column
            $table->text('remarks')->nullable();
            // Column for user id who created the application Interview details
            $table->integer('created_by')->default(0);
            // Column for user id who modified the application Interview details
            $table->integer('modified_by')->default(0);
            // application Interview created time and modified time columns 
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
        Schema::dropIfExists('application_interviews');
    }
};
