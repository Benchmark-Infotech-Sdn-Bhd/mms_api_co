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
        Schema::create('vendor_attachments', function (Blueprint $table) {
            // vendor attachments id column
            $table->id();
            // vendor attachments file id column
            $table->bigInteger('file_id')->unsigned()->nullable();
            // Foreign key from vendors table
            $table->foreign('file_id')->references('id')->on('vendors')->onDelete('cascade');
            // vendor attachments file name column
            $table->string('file_name', 255);
            // vendor attachments file type column
            $table->string('file_type', 255);
            // vendor attachments file url column
            $table->text('file_url')->nullable();
            // Column for user id who created the vendor attachments 
            $table->integer('created_by')->default(0);
            // Column for user id who modified the vendor attachments
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
        Schema::dropIfExists('vendor_attachments');
    }
};
