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
            // CRM prospect services id column
            $table->id();
            // CRM prospect id column
            $table->bigInteger('crm_prospect_id')->unsigned();
            // Foreign key form crm_prospects table
            $table->foreign('crm_prospect_id')->references('id')->on('crm_prospects')->onDelete('cascade');
            // Service id column
            $table->bigInteger('service_id')->nullable()->unsigned()->index();
            // Service name column
            $table->string('service_name', 255)->nullable();
            // CRM prospect service status column
            $table->tinyInteger('status')->default(0)->unsigned()->index();
            // CRM prospect service created time and modified time columns
            $table->timestamps();
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
