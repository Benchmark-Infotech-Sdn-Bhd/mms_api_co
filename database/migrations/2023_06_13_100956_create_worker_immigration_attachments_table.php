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
        Schema::create('worker_immigration_attachments', function (Blueprint $table) {
            // attachments id column
            $table->id();
             // attachments file id column
             $table->bigInteger('file_id')->unsigned()->nullable();
             // Foreign key from workers table
             $table->foreign('file_id')->references('id')->on('workers')->onDelete('cascade');
             // insurance attachments file name column
             $table->string('file_name', 255);
             // attachments file type column
             $table->string('file_type', 255);
             // attachments file url column
             $table->text('file_url')->nullable();
             // Column for user id who created the attachments 
             $table->integer('created_by')->default(0);
             // Column for user id who modified the attachments 
             $table->integer('modified_by')->default(0);
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
        Schema::dropIfExists('worker_immigration_attachments');
    }
};
