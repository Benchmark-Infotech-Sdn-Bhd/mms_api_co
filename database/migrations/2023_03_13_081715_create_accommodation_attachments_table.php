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
        Schema::create('accommodation_attachments', function (Blueprint $table) {
            // accommodation attachments id column
            $table->id();
            // accommodation attachments file id column
            $table->bigInteger('file_id')->unsigned()->nullable();
            // Foreign key from accommodation table
            $table->foreign('file_id')->references('id')->on('accommodation')->onDelete('cascade');
            // accommodation attachments file name column
            $table->string('file_name', 255);
            // accommodation attachments file type column
            $table->string('file_type', 255);
            // accommodation attachments file url column
            $table->text('file_url')->nullable();
            // Column for user id who created the accommodation attachments 
            $table->integer('created_by')->default(0);
            // Column for user id who modified the accommodation attachments 
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
        Schema::dropIfExists('accommodation_attachments');
    }
};
