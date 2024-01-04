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
        Schema::create('e-contract_applications', function (Blueprint $table) {
            // Id column for e-Contract application
            $table->id();
            // CRM prospect id column
            $table->bigInteger('crm_prospect_id')->unsigned();
            // Foreign key from crm prospect table
            $table->foreign('crm_prospect_id')->references('id')->on('crm_prospects')->onDelete('cascade');
            // Column for service id
            $table->bigInteger('service_id')->unsigned();
            // Foreign key from crm prospect service table
            $table->foreign('service_id')->references('id')->on('crm_prospect_services')->onDelete('cascade');
            // Column for person in charge
            $table->string('person_incharge',255)->nullable();
            // Column for requested quota
            $table->integer('quota_requested')->nullable();
            // Column for cost quoted
            $table->float('cost_quoted')->default(0.0);
            // Column for application status
            $table->string('status')->default('Proposal')->index();
            // Column for remarks
            $table->string('remarks',255)->nullable();
            // Column for user id who created the application
            $table->integer('created_by')->default(0);
            // Column for user id who modified the application
            $table->integer('modified_by')->default(0);
            // Column for application created and modified time
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
        //
    }
};
