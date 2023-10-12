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
        Schema::table('worker_bulk_upload', function (Blueprint $table) {
            //$table->dropUnique('worker_bulk_upload_agent_id_foreign');
            $table->dropForeign('worker_bulk_upload_agent_id_foreign');
            //$table->dropIndex('worker_bulk_upload_agent_id_index');

            // Foreign key from directrecruitment Onboarding Agent table
            $table->foreign('agent_id')
              ->references('id')->on('directrecruitment_onboarding_agent')->onDelete('cascade');
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
