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
        Schema::create('application_interview_attachments', function (Blueprint $table) {
            // application interview attachments id column
            $table->id();
            // application interview attachments file id column
            $table->bigInteger('file_id')->unsigned()->nullable();
            // Foreign key from application interview table
            $table->foreign('file_id')->references('id')->on('application_interviews')->onDelete('cascade');
            // application interview attachments file name column
            $table->string('file_name', 255);
            // application interview attachments file type column
            $table->string('file_type', 255);
            // application interview attachments file url column
            $table->text('file_url')->nullable();
            // Column for user id who created the application interview attachments 
            $table->integer('created_by')->default(0);
            // Column for user id who modified the application interview attachments 
            $table->integer('modified_by')->default(0);
            // vendor created time and modified time columns 
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
        Schema::dropIfExists('application_interview_attachments');
    }
};
