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
        Schema::table('directrecruitment_arrival', function (Blueprint $table) {
            // arrival time column
            $table->time('arrival_time')->nullable()->after('flight_date')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('directrecruitment_arrival', function (Blueprint $table) {
            //
        });
    }
};
