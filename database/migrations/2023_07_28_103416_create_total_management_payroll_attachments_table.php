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
        Schema::create('total_management_payroll_attachments', function (Blueprint $table) {
            // Id column for total managemnt payroll attachment
            $table->id();
            // Column for month 
            $table->integer('month')->default(0);
            // Column for year
            $table->integer('year')->default(0);
            // Column for project id
            $table->bigInteger('file_id')->unsigned()->nullable();
            // Foreign key from total managemnt project table
            $table->foreign('file_id')->references('id')->on('total_management_project')->onDelete('cascade');
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
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            Schema::dropIfExists('total_management_payroll_attachments');
        }
    }
};
