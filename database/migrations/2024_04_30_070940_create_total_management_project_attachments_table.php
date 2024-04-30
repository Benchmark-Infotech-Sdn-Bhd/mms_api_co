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
        Schema::create('total_management_project_attachments', function (Blueprint $table) {
            // total management project attachments id column
            $table->id();
            // total management project attachments file id column
            $table->bigInteger('file_id')->unsigned()->nullable();
            // Foreign key from total_management_project table
            $table->foreign('file_id')->references('id')->on('total_management_project')->onDelete('cascade');
            // total management project attachments file name column
            $table->string('file_name', 255)->nullable();
            // total management project attachments file type column
            $table->string('file_type', 255)->nullable();
            // total management project attachments file url column
            $table->text('file_url')->nullable();
            // Column for user id who created the total management project attachments 
            $table->integer('created_by')->default(0);
            // Column for user id who modified the total management project attachments 
            $table->integer('modified_by')->default(0);
            // total management project created time and modified time columns 
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
        Schema::dropIfExists('total_management_project_attachments');
    }
};
