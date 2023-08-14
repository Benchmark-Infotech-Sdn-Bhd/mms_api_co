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
        Schema::create('total_management_cost_management_attachments', function (Blueprint $table) {
            // Cost management attachments id column
            $table->id();
            // Cost management attachments file id column
            $table->bigInteger('file_id')->unsigned()->nullable();
            // Foreign key from Cost management table
            $table->foreign('file_id')->references('id')->on('total_management_cost_management')->onDelete('cascade');
            // Cost management attachments file name column
            $table->string('file_name', 255);
            // Cost management attachments file type column
            $table->string('file_type', 255);
            // Cost management attachments file url column
            $table->text('file_url')->nullable();
            // Column for user id who created the Cost management attachments 
            $table->integer('created_by')->default(0);
            // Column for user id who modified the Cost management attachments 
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
        Schema::dropIfExists('total_management_cost_management_attachments');
    }
};
