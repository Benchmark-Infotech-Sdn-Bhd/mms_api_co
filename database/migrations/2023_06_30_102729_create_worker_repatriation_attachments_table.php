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
        Schema::create('worker_repatriation_attachments', function (Blueprint $table) {
            // Column for worker repatriation attachments id
            $table->id();
            // worker attachments file id column
            $table->bigInteger('file_id')->unsigned()->nullable();
            // Foreign key from Worker table
            $table->foreign('file_id')->references('id')->on('workers')->onDelete('cascade');
            // Worker attachments file name column
            $table->string('file_name', 255);
            // Worker attachments file type column
            $table->string('file_type', 255);
            // Worker attachments file url column
            $table->text('file_url')->nullable();
            // Column for user id who created the attachment
            $table->integer('created_by')->default(0);
            // Column for user id who modified the attachment
            $table->integer('modified_by')->default(0);
            // attachment created and modifies time
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
        Schema::dropIfExists('worker_repatriation_attachments');
    }
};
