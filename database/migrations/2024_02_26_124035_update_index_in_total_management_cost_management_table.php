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
        Schema::table('total_management_cost_management', function (Blueprint $table) {
            $table->index(['payment_reference_number']);
            $table->index(['month']);
            $table->index(['year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('total_management_cost_management', function (Blueprint $table) {
            $table->dropIndex(['payment_reference_number']);
            $table->dropIndex(['month']);
            $table->dropIndex(['year']); 
        });
    }
};
