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
        Schema::create('fwcms', function (Blueprint $table) {
            // FWCMS Id column
            $table->id();
            // Direct Recruitment Application Id column
            $table->unsignedBigInteger('application_id');
            // Foreign key from Direct Recruitment Application table
            $table->foreign('application_id')
              ->references('id')->on('directrecruitment_applications')->onDelete('cascade');
            // Submission Date column
            $table->date('submission_date');
            // Applied Quota column
            $table->integer('applied_quota');
            // Status column
            $table->enum('status',['Submitted', 'Query', 'Rejected', 'Approved'])->default('Submitted');
            // KSM reference number column
            $table->string('ksm_reference_number', 255);
            // Remarks column
            $table->text('remarks')->nullable();
            // Column for user id who created the FWCMS details
            $table->integer('created_by')->default(0);
            // Column for user id who modified the FWCMS details
            $table->integer('modified_by')->default(0);
            // FWCMS created time and modified time columns 
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
        Schema::dropIfExists('fwcms');
    }
};
