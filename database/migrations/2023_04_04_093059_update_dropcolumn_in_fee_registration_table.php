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
        Schema::table('fee_registration', function (Blueprint $table) {
            if (DB::getDriverName() !== 'sqlite') {
                $table->dropColumn('applicable_for');
                $table->dropColumn('sectors');
            }
        });
    } 
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fee_registration', function (Blueprint $table) {
            //
        });
    }
};
