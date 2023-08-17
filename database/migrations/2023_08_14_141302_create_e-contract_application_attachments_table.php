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
        Schema::create('e-contract_application_attachments', function (Blueprint $table) {
            // e-contract attachments id column
            $table->id();
            // e-contract attachments file id column
            $table->bigInteger('file_id')->unsigned()->nullable();
            // Foreign key from e-contract table
            $table->foreign('file_id')->references('id')->on('e-contract_applications')->onDelete('cascade');
            // e-contract attachments file name column
            $table->string('file_name', 255);
            // e-contract attachments file type column
            $table->string('file_type', 255);
            //  e-contract attachments file url column
            $table->text('file_url')->nullable();
            // Column for user id who created the e-contract attachments 
            $table->integer('created_by')->default(0);
            // Column for user id who modified the  e-contract attachments 
            $table->integer('modified_by')->default(0);
            // e-contract created time and modified time columns 
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
        //
    }
};
