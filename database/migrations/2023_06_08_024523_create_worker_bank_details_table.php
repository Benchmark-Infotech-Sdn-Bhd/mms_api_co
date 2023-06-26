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
        Schema::create('worker_bank_details', function (Blueprint $table) {
            // Worker bank details id column
            $table->id();
            // worker bank details workerid column
            $table->bigInteger('worker_id')->unsigned()->nullable();
            // Foreign key from Worker table
            $table->foreign('worker_id')->references('id')->on('workers')->onDelete('cascade');            
            // Column for bank name
            $table->string('bank_name', 255)->nullable();
            // Column for account number
            $table->string('account_number', 255)->nullable();
            // Column for socso number
            $table->string('socso_number', 255)->nullable();
            // Column for user id who created the worker bank details
            $table->integer('created_by')->default(0);
            // Column for user id who modified the worker bank details
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
        Schema::dropIfExists('worker_bank_details');
    }
};
