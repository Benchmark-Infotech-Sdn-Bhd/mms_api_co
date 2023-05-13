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
        Schema::table('directrecruitment_application_checklist', function (Blueprint $table) {
            $table->date('submitted_on')->nullable();
            $table->date('modified_on')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('directrecruitment_application_checklist', function (Blueprint $table) {
            //
        });
    }
};
