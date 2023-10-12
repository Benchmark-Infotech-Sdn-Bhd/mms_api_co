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
        Schema::table('directrecruitment_workers', function (Blueprint $table) {
            $table->dropUnique('directrecruitment_workers_agent_id_foreign');
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
