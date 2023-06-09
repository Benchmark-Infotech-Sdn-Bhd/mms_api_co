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
        Schema::create('worker_visa_attachments', function (Blueprint $table) {
            // Worker visa attachments id column
            $table->id();
            // worker visa attachments file id column
            $table->bigInteger('file_id')->unsigned()->nullable();
            // Foreign key from Worker visa table
            $table->foreign('file_id')->references('id')->on('worker_visa')->onDelete('cascade');
            // Worker visa attachments file name column
            $table->string('file_name', 255);
            // Worker visa attachments file type column
            $table->string('file_type', 255);
            // Worker visa attachments file url column
            $table->text('file_url')->nullable();
            // Column for user id who created the Worker visa attachments 
            $table->integer('created_by')->default(0);
            // Column for user id who modified the Worker visa attachments 
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
        Schema::dropIfExists('worker_visa_attachments');
    }
};
