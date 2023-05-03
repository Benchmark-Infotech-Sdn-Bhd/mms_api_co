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
        Schema::create('directrecruitment_application_checklist', function (Blueprint $table) {
            // Direct Recruitment Application Checklist Id column
            $table->id();
            // Direct Recruitment Application Checklist application_id column
            $table->unsignedBigInteger('application_id');
            // Direct Recruitment Application Checklist Item name column
            $table->string('item_name', 255);
            // Direct Recruitment Application Checklist Application Status column
            $table->enum('application_checklist_status',['Pending','Completed'])->default('Pending');
            // Direct Recruitment Application Checklist Remarks column
            $table->text('remarks')->nullable();
            // Direct Recruitment Application Checklist File url column
            $table->text('file_url')->nullable();
            // Column for user id who created the Direct Recruitment Application Checklist
            $table->integer('created_by')->default(0);
            // Column for user id who modified the Direct Recruitment Application Checklist
            $table->integer('modified_by')->default(0);
            // Foreign key from Direct Recruitment Application Checklist table
            $table->foreign('application_id')
              ->references('id')->on('directrecruitment_applications')->onDelete('cascade');
            // Direct Recruitment Application Checklist created_at and updated_at columns
            $table->timestamps();
            // softdelete for Direct Recruitment Application Checklist
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('directrecruitment_application_checklist');
    }
};
