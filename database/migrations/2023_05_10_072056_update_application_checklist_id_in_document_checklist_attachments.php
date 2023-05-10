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
        Schema::table('document_checklist_attachments', function (Blueprint $table) {
            // Foreign key from directrecruitment_application_checklist table
            $table->foreignId('application_checklist_id')->nullable()
                ->references('id')->on('directrecruitment_application_checklist')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('document_checklist_attachments', function (Blueprint $table) {
            //
        });
    }
};
