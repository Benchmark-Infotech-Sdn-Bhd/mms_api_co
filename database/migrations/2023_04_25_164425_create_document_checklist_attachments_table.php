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
        Schema::create('document_checklist_attachments', function (Blueprint $table) {
            // DocumentChecklist-Attachment Id column
            $table->id();
            // DocumentChecklist-Attachment Document checklist id column
            $table->unsignedBigInteger('document_checklist_id');
            // DocumentChecklist-Attachment Application id column
            $table->unsignedBigInteger('application_id');
            // DocumentChecklist-Attachment File type column
            $table->string('file_type', 255);
            // DocumentChecklist-Attachment File url column
            $table->text('file_url')->nullable();
            // Column for user id who created the DocumentChecklist-Attachment
            $table->integer('created_by')->default(0);
            // Column for user id who modified the DocumentChecklist-Attachment
            $table->integer('modified_by')->default(0);
            // Foreign key from Document checklist table
            $table->foreign('document_checklist_id')
              ->references('id')->on('document_checklist')->onDelete('cascade');
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
        Schema::dropIfExists('document_checklist_attachments');
    }
};
