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
        Schema::create('worker_insurance_details', function (Blueprint $table) {
            // Worker fomema id column
            $table->id();
            // worker fomema workerid column
            $table->bigInteger('worker_id')->unsigned()->nullable();
            // Foreign key from Worker table
            $table->foreign('worker_id')->references('id')->on('workers')->onDelete('cascade');            
            // Column for ig policy number
            $table->string('ig_policy_number', 255)->nullable();
            // Column for ig policy number validity date column
            $table->date('ig_policy_number_valid_until')->nullable();
            // Column for hospitalization policy number
            $table->string('hospitalization_policy_number', 255)->nullable();
            // Column for hospitalization policy number validity date column
            $table->date('hospitalization_policy_number_valid_until')->nullable();
            $table->integer('created_by')->default(0);
            // Column for user id who modified the Worker fomema
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
        Schema::dropIfExists('worker_insurance_details');
    }
};
