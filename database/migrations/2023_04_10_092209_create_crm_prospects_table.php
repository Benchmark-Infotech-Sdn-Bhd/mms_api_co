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
        Schema::create('crm_prospects', function (Blueprint $table) {
            // CRM prospect id column
            $table->id();
            // Company name column
            $table->string('company_name', 255);
            // ROC column
            $table->string('roc_number', 255);
            // Director or Company Owner column
            $table->string('director_or_owner', 255);
            // Contact number column
            $table->string('contact_number')->nullable();  
            // Email id Column
            $table->string('email', 250)->unique();
            // Address Column
            $table->text('address');
            // CRM prospect status column
            $table->tinyInteger('status')->default(1)->unsigned()->index();
            // Person in charge name column
            $table->string('pic_name', 255);
            // Person in charge contact number column
            $table->string('pic_contact_number')->nullable();
            // Person in charge designation
            $table->string('pic_designation', 255);
            // CRM prospect registered by id column
            $table->bigInteger('registered_by')->unsigned()->nullable();
            // Foreign key from employee table
            $table->foreign('registered_by')->references('id')->on('employee')->onDelete('cascade');
            // Sector id column
            $table->bigInteger('sector_type')->unsigned()->nullable();
            // Foreign key from sectors table
            $table->foreign('sector_type')->references('id')->on('sectors')->onDelete('cascade');
            // Column for user id who created the prospect
             $table->integer('created_by')->default(0);
            // Column for user id who modified the prospect
             $table->integer('modified_by')->default(0);
            // CRM prospect created time and modified time columns
            $table->timestamps();
            // For soft delete
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crm_prospects');
    }
};
