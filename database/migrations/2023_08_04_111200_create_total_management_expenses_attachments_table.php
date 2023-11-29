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
        Schema::create('total_management_expenses_attachments', function (Blueprint $table) {
            // Expense attachments id column
            $table->id();
            // Expense attachments file id column
            $table->bigInteger('file_id')->unsigned()->nullable();
            // Foreign key from Expense table
            $table->foreign('file_id')->references('id')->on('directrecruitment_expenses')->onDelete('cascade');
            // Expense attachments file name column
            $table->string('file_name', 255);
            // Expense attachments file type column
            $table->string('file_type', 255);
            // Expense attachments file url column
            $table->text('file_url')->nullable();
            // Column for user id who created the Expense attachments 
            $table->integer('created_by')->default(0);
            // Column for user id who modified the Expense attachments 
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
        if (DB::getDriverName() !== 'sqlite') {
            Schema::dropIfExists('total_management_expenses_attachments');
        }
    }
};
