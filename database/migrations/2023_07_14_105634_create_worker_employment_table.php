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
        Schema::create('worker_employment', function (Blueprint $table) {
            // column for id
            $table->id();
            // column worker id
            $table->bigInteger('worker_id')->unsigned()->nullable();
            // Foreign key from worker table
            $table->foreign('worker_id')->references('id')->on('workers')->onDelete('cascade');
            // column for project id
            $table->bigInteger('project_id')->unsigned()->nullable();
            if (DB::getDriverName() !== 'sqlite') {
                // Foreign key from project table
                $table->foreign('project_id')->references('id')->on('total_management_project')->onDelete('cascade');
            }
            // Column for department
            $table->string('department')->nullable();
            // Column for sub department
            $table->string('sub_department')->nullable();
            // Column for accommodation provider id 
            $table->integer('accommodation_provider_id')->default(0);
            // Column for accommodation unit id 
            $table->integer('accommodation_unit_id')->default(0);
            // Column for work start date 
            $table->date('work_start_date')->nullable();
            // Column for work end date
            $table->date('work_end_date')->nullable();
            // Column for user id who created the employment 
            $table->integer('created_by')->default(0);
            // Column for user id who modified the employment 
            $table->integer('modified_by')->default(0);
            // Column for employment created and modified time
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
        Schema::dropIfExists('worker_employment');
    }
};
