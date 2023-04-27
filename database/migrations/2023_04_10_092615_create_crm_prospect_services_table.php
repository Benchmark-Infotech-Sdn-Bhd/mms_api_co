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
        Schema::create('crm_prospect_services', function (Blueprint $table) {
           // CRM prospect Sector id column
           $table->id();
           // CRM prospect id column
           $table->bigInteger('crm_prospect_id')->unsigned();
           // Foreign key form crm_prospects table
           $table->foreign('crm_prospect_id')->references('id')->on('crm_prospects')->onDelete('cascade');
           // Service id column
           $table->bigInteger('service_id')->unsigned()->nullable();
           // Service name column
           $table->string('service_name', 255)->nullable();
           // Sector id column
           $table->bigInteger('sector_id')->unsigned();
           // Foreign key from sectors table
           $table->foreign('sector_id')->references('id')->on('sectors')->onDelete('cascade');
           // Sector name column
           $table->string('sector_name', 255)->nullable();
           // Contract type column
           $table->enum('contract_type',['Zero Cost', 'Normal', 'No Contract'])->default('No Contract');
           // CRM prospect sector created time and modified time columns 
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
        Schema::dropIfExists('crm_prospect_services');
    }
};
