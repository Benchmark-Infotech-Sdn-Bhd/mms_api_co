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
        Schema::table('company', function (Blueprint $table) {
            $table->index(['company_name']);
            $table->index(['pic_name']);
            $table->index(['parent_flag']);
            $table->index(['parent_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('company', function (Blueprint $table) {
            $table->dropIndex(['company_name']);
            $table->dropIndex(['pic_name']);
            $table->dropIndex(['parent_flag']);
            $table->dropIndex(['parent_id']);
        });
    }
};
