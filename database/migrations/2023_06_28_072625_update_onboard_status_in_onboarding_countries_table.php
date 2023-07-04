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
        Schema::table('directrecruitment_onboarding_countries', function (Blueprint $table) {
            // Column for calling visa cancel status
            $table->tinyInteger('onboarding_status')->default(1)->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('directrecruitment_onboarding_countries', function (Blueprint $table) {
            //
        });
    }
};
