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
        Schema::create('worker_post_arrival_attachments', function (Blueprint $table) {
            // Worker post arrival attachments id column
            $table->id();
            // worker post arrival attachments file id column
            $table->bigInteger('file_id')->unsigned()->nullable();
            // Foreign key from Worker post arrival table
            $table->foreign('file_id')->references('id')->on('worker_post_arrival')->onDelete('cascade');
            // Worker post arrival attachments file name column
            $table->string('file_name', 255);
            // Worker post arrival attachments file type column
            $table->string('file_type', 255);
            // Worker post arrival attachments file url column
            $table->text('file_url')->nullable();
            // Column for user id who created the Worker post arrival attachments 
            $table->integer('created_by')->default(0);
            // Column for user id who modified the Worker post arrival attachments 
            $table->integer('modified_by')->default(0);
            // post arrival attachment created time and modified time columns 
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
        Schema::dropIfExists('worker_post_arrival_attachments');
    }
};
