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
        Schema::table('workers', function (Blueprint $table) {
            // CRM prospect id column
            $table->bigInteger('crm_prospect_id')->unsigned()->nullable();
            // Foreign key from crm prospect table
            $table->foreign('crm_prospect_id')->references('id')->on('crm_prospects')->onDelete('cascade');
            // Country Id column
            $table->unsignedBigInteger('onboarding_country_id')->nullable()->change();
            // Direct Recruitment Agent Id column
            $table->unsignedBigInteger('agent_id')->nullable()->change();
            // Direct Recruitment Application Id column
            $table->unsignedBigInteger('application_id')->nullable()->change();
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
