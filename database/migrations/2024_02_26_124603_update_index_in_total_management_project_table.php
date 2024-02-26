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
        Schema::table('total_management_project', function (Blueprint $table) {
            $table->index(['supervisor_id']);
            $table->index(['name']);
            $table->index(['state']);
            $table->index(['city']);
        });
    }
    
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('total_management_project', function (Blueprint $table) {
            $table->dropIndex(['supervisor_id']);
            $table->dropIndex(['name']); 
            $table->dropIndex(['state']);
            $table->dropIndex(['city']); 
        });
    }
};
