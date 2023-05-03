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
        Schema::create('crm_prospect_attachments', function (Blueprint $table) {
            // CRM prospect attachment id column
            $table->id();
            // CRM prospect attachments file id column
            $table->bigInteger('file_id')->unsigned()->nullable();
            // Foreign key from crm prospect table
            $table->foreign('file_id')->references('id')->on('crm_prospects')->onDelete('cascade');
            // CRM prospect attachments file name column
            $table->string('file_name', 255);
            // CRM prospect attachments file type column
            $table->string('file_type', 255);
            // CRM prospect attachments file url column
            $table->text('file_url')->nullable();
            // Column for user id who created the crm prospect attachments 
            $table->integer('created_by')->default(0);
            // Column for user id who modified the crm prospect attachments 
            $table->integer('modified_by')->default(0);
            // CRM prospect attachment created time and modified time columns 
            $table->timestamps();
            // for soft delete
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crm_prospect_attachments');
    }
};
