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
        Schema::create('directrecruitment_application_approval', function (Blueprint $table) {
            // Direct Recruitment Application Approval Id column
            $table->id();
            // Direct Recruitment Application Approval application_id column
            $table->unsignedBigInteger('application_id');
            // Foreign key from Direct Recruitment Application table
            $table->foreign('application_id')
              ->references('id')->on('directrecruitment_applications')->onDelete('cascade');
            // Direct Recruitment Application Approval Item name column
            $table->string('item_name', 255);
            // Direct Recruitment Application Approval KSM reference number column
            $table->string('ksm_reference_number', 255);
            // Direct Recruitment Application Approval Received Date column
            $table->date('received_date');
            // Direct Recruitment Application Approval Valid Until column
            $table->date('valid_until');
            // Column for user id who created the Direct Recruitment Application Approval
            $table->integer('created_by')->default(0);
            // Column for user id who modified the Direct Recruitment Application Approval
            $table->integer('modified_by')->default(0);
            // Direct Recruitment Application Approval created_at and updated_at columns
            $table->timestamps();
            // softdelete for Direct Recruitment Application Approval
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('directrecruitment_application_approval');
    }
};
