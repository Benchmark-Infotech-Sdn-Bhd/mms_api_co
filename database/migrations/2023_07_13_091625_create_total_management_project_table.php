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
        Schema::create('total_management_project', function (Blueprint $table) {
            // Column for ID project
            $table->id();
            // Column for application id
            $table->bigInteger('application_id')->unsigned();
            // Foreign key from total_management_applications table
            $table->foreign('application_id')->references('id')->on('total_management_applications')->onDelete('cascade');
            // Column for name
            $table->string('name',255)->nullable();
            // Column for state
            $table->string('state',255)->nullable();
            // Column for city
            $table->string('city',255)->nullable();
            // Cloumn for address
            $table->string('address',255)->nullable();
            // Column for employee_id
            $table->bigInteger('employee_id')->unsigned()->nullable();
            // Foreign key from employee table
            $table->foreign('employee_id')->references('id')->on('employee')->onDelete('cascade');
            // Column for transportation_provider_id
            $table->bigInteger('transportation_provider_id')->unsigned()->nullable();
            // Foreign key from vendors table
            $table->foreign('transportation_provider_id')->references('id')->on('vendors')->onDelete('cascade');
            // Column for driver_id
            $table->bigInteger('driver_id')->unsigned()->nullable();
            // Foreign key from transportation table
            $table->foreign('driver_id')->references('id')->on('transportation')->onDelete('cascade');
            // Column for assign_as_supervisor
            $table->tinyInteger('assign_as_supervisor')->default(0);
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
        if (DB::getDriverName() !== 'sqlite') {
            Schema::dropIfExists('total_management_project');
        }
    }
};
