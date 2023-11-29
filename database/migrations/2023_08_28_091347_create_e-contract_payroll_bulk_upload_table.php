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
        Schema::create('e-contract_payroll_bulk_upload', function (Blueprint $table) {
            // Column for Id 
            $table->id();
            // Project Id column
            $table->unsignedBigInteger('project_id');
            // Foreign key from e-contract project table
            $table->foreign('project_id')
              ->references('id')->on('e-contract_project')->onDelete('cascade');
            // Column for bulk upload name
            $table->string('name', 255);
            // Column for Bulk upload Type 
            $table->string('type', 255);
            // Column for Total Records
            $table->integer('total_records')->unsigned()->default(0);
            // Column for Total Success
            $table->integer('total_success')->unsigned()->default(0);
            // Column for Total Failure
            $table->integer('total_failure')->unsigned()->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            Schema::dropIfExists('e-contract_payroll_bulk_upload');
        }
    }
};
