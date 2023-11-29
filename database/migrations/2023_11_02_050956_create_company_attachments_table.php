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
        Schema::create('company_attachments', function (Blueprint $table) {
            // Id column
            $table->id();
            // file id column
            $table->bigInteger('file_id')->unsigned()->nullable();
            // Foreign key from company table
            $table->foreign('file_id')->references('id')->on('company')->onDelete('cascade');
            // Column for file name
            $table->string('file_name', 255);
            // Column for file type
            $table->string('file_type', 255);
            // Column for file url
            $table->text('file_url')->nullable();
            // Column for user id who created the attachment
            $table->integer('created_by')->default(0);
            // Column for user id who created the attachment
            $table->integer('modified_by')->default(0);
            // Column for attachment created and modified time
            $table->timestamps();
            // for softdelete
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            Schema::dropIfExists('company_attachments');
        }
    }
};
