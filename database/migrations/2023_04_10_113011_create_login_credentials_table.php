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
        Schema::create('login_credentials', function (Blueprint $table) {
            // login credentials id column
            $table->id();
            // CRM prospect id column
            $table->bigInteger('crm_prospect_id')->unsigned();
            // Foreign key from crm_prospects table
            $table->foreign('crm_prospect_id')->references('id')->on('crm_prospects')->onDelete('cascade');
            // System id column
            $table->bigInteger('system_id')->unsigned();
            // Foreign key from table System Type
            $table->foreign('system_id')->references('id')->on('system_type')->onDelete('cascade');
            // system name column
            $table->string('system_name', 255);
            // Username column
            $table->string('username', 255);
            // Password column
            $table->string('password')->nullable();
            // Login credentials created time and modified time columns
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('login_credentials');
    }
};
