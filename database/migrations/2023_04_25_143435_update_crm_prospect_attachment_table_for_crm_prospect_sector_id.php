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
        Schema::table('crm_prospect_attachments', function (Blueprint $table) {
            // CRM prospect sector id column
            $table->bigInteger('prospect_service_id')->unsigned()->nullable()->after('file_id');
            // Foreign key form crm_prospect_service table
           $table->foreign('prospect_service_id')->references('id')->on('crm_prospect_services')->onDelete('cascade');
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
