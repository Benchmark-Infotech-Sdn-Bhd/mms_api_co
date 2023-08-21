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
        Schema::create('e-contract_project', function (Blueprint $table) {
            // Column for ID project
            $table->id();
            // Column for application id
            $table->bigInteger('application_id')->unsigned();
            // Foreign key from total_management_applications table
            $table->foreign('application_id')->references('id')->on('e-contract_applications')->onDelete('cascade');
            // Column for name
            $table->string('name',255)->nullable();
            // Column for state
            $table->string('state',255)->nullable();
            // Column for city
            $table->string('city',255)->nullable();
            // Cloumn for address
            $table->string('address',255)->nullable();
            // column for annual leave
            $table->integer('annual_leave')->default(0);
            // column for medical leave
            $table->integer('medical_leave')->default(0);
            // column for hospitalization leave
            $table->integer('hospitalization_leave')->default(0);
            // Column for user id who created the project
            $table->integer('created_by')->default(0);
            // Column for user id who modified the project
            $table->integer('modified_by')->default(0);
            // Column for project created and modified time
            $table->timestamps();
            // Colimn for softdelete
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('e-contract_project');
    }
};
