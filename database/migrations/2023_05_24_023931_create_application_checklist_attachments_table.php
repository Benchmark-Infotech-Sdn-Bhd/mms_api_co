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
        Schema::create('application_checklist_attachments', function (Blueprint $table) {
            // DirectRecruitmentApplicationChecklist-Attachment Id column
            $table->id();
            // DirectRecruitmentApplicationChecklist-Attachment Application id column
            $table->unsignedBigInteger('application_id');
            // DirectRecruitmentApplicationChecklist-Attachment Application checklist id column
            $table->unsignedBigInteger('application_checklist_id');
            // DirectRecruitmentApplicationChecklist-Attachment Document checklist id column
            $table->unsignedBigInteger('document_checklist_id');
            // DirectRecruitmentApplicationChecklist-Attachment File type column
            $table->string('file_type', 255);
            // DirectRecruitmentApplicationChecklist-Attachment File url column
            $table->text('file_url')->nullable();
            // Column for user id who created the DirectRecruitmentApplicationChecklist-Attachment
            $table->integer('created_by')->default(0);
            // Column for user id who modified the DirectRecruitmentApplicationChecklist-Attachment
            $table->integer('modified_by')->default(0);
            // Foreign key from DirectRecruitmentApplicationChecklist table
            $table->foreign('application_checklist_id', 'application_checklist_id_foreign')
              ->references('id')->on('directrecruitment_application_checklist')->onDelete('cascade');
            // DocumentChecklist-Attachment created_at and updated_at columns
            $table->timestamps();
            // softdelete for DocumentChecklist-Attachment
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('application_checklist_attachments');
    }
};
