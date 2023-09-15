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
        Schema::create('employee', function (Blueprint $table) {
            // Employee Id column
            $table->id();
            // Employee Name column
            $table->string('employee_name',255)->index();
            // Employee Gender column
            $table->string('gender',15);
            // Employee Date of birth column
            $table->date('date_of_birth');
            // Employee IC number column
            $table->bigInteger('ic_number')->default(0)->index();
            // Employee Passport number column
            $table->string('passport_number')->nullable()->index();
            // Employee Email column
            $table->string('email',150)->index();
            // Employee contact number column
            $table->bigInteger('contact_number')->default(0);
            // Employee Address column
            $table->text('address');
            // Employee postcode column
            $table->mediumInteger('postcode');
            // Employee position column
            $table->string('position',150);
            // Employee Branch Id column
            $table->unsignedBigInteger('branch_id')->index();
            // Employee Role Id column
            $table->unsignedBigInteger('role_id')->index();
            // Employee Salary column
            $table->float('salary');
            // Employee Status column
            $table->tinyInteger('status')->default(1)->unsigned()->index();
            // Employee city column
            $table->string('city',150)->nullable();
            // Employee state column
            $table->string('state',150);
            // Column for user Id, who created the employee
            $table->integer('created_by')->default(0);
            // Column for user Id, who updated the employee
            $table->integer('modified_by')->default(0);
            // Countries created_at and updated_at columns
            $table->timestamps();
            // Unique field for employee
            $table->unique(['email', 'deleted_at']);
            // Foreign key from Branch table
            $table->foreign('branch_id')
              ->references('id')->on('branch');
            $table->foreign('role_id')
            // Foreign key from Roles table
              ->references('id')->on('roles');
            // Indexing for Employee based on Id, Branch id, Role id columns
            $table->index(['id']);
            // softdelete for Employee
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
      if (DB::getDriverName() !== 'sqlite') {
        Schema::dropIfExists('employee');
      }
    }
};
