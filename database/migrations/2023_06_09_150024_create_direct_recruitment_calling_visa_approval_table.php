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
        Schema::create('direct_recruitment_calling_visa_approval', function (Blueprint $table) {
            // Column for calling visa approval Id
            $table->id();
            // Column for worker Id 
            $table->unsignedBigInteger('worker_id');
            // Foreign key from Workers table
            $table->foreign('worker_id')
              ->references('id')->on('workers')->onDelete('cascade');
            // Column for calling visa approval status
            $table->enum('status',['Pending', 'Approved', 'Rejected'])->default('Pending');
            // Column for calling visa generated date
            $table->date('calling_visa_generated')->nullable();
            // Column for calling visa valid until
            $table->date('calling_visa_valid_until')->nullable();
            // Column for remarks
            $table->text('remarks')->nullable();
            // Column for user id who created the calling visa approval
            $table->integer('created_by')->default(0);
            // Column for user id who modified the calling visa approval
            $table->integer('modified_by')->default(0);
            // calling visa approval created time and modified time columns
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
        Schema::dropIfExists('direct_recruitment_calling_visa_approval');
    }
};
