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
        Schema::create('e-contract_cost_management_attachments', function (Blueprint $table) {
            // e-contract attachments id column
            $table->id();
            // e-contract expenses attachments file id column
            $table->bigInteger('file_id')->unsigned()->nullable();
            // Foreign key from e-contract_cost_management table
            $table->foreign('file_id')->references('id')->on('e-contract_cost_management')->onDelete('cascade');
            // e-contract expenses attachments file name column
            $table->string('file_name', 255)->nullable();
            // e-contract expenses attachments file type column
            $table->string('file_type', 255)->nullable();
            //  e-contract expenses attachments file url column
            $table->text('file_url')->nullable();
            // Column for user id who created the e-contract expenses attachments 
            $table->integer('created_by')->default(0);
            // Column for user id who modified the  e-contract expenses attachments 
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
        if (DB::getDriverName() !== 'sqlite') {
            Schema::dropIfExists('contract_cost_management_attachments');
        }
    }
};
