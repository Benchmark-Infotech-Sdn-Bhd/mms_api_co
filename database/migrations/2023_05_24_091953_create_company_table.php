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
        Schema::create('company', function (Blueprint $table) {
            // Column for Id 
            $table->id();
            // Column for company name
            $table->string('company_name',255);
            // Column for register number
            $table->string('register_number',255);
            // Column for country
            $table->string('country', 255);
            // Column for state
            $table->string('state', 255);
            // Column for PIC name
            $table->string('pic_name', 255);
            // Column for role name
            $table->string('role', 255);
            // Column for Company status
            $table->tinyInteger('status')->default(1);
            // Column for user id who created the Company
            $table->integer('created_by')->default(0);
            // Column for user id who modified the Company
            $table->integer('modified_by')->default(0);
            // Company created time and modified time columns 
            $table->timestamps();
            // for softdelete
            $table->softDeletes();
            // Unique field for users
            $table->unique(['register_number', 'deleted_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company');
    }
};
