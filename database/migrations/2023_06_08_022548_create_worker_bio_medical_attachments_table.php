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
        Schema::create('worker_bio_medical_attachments', function (Blueprint $table) {
            // Worker bio medical attachments id column
            $table->id();
            // worker bio medical attachments file id column
            $table->bigInteger('file_id')->unsigned()->nullable();
            // Foreign key from bio medical table
            $table->foreign('file_id')->references('id')->on('worker_bio_medical')->onDelete('cascade');
            // Worker bio medical attachments file name column
            $table->string('file_name', 255);
            // Worker bio medical attachments file type column
            $table->string('file_type', 255);
            // Worker bio medical attachments file url column
            $table->text('file_url')->nullable();
            // Column for user id who created the Worker bio medical attachments 
            $table->integer('created_by')->default(0);
            // Column for user id who modified the Worker bio medical attachments 
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
        Schema::dropIfExists('worker_bio_medical_attachments');
    }
};
