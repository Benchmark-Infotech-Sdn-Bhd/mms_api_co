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
        Schema::table('worker_employment', function (Blueprint $table) {
            $table->index(['service_type']);
            $table->index(['transfer_flag']);
            $table->index(['event_type']);
            $table->index(['department']);
            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('worker_employment', function (Blueprint $table) {
            $table->dropIndex(['service_type']);
            $table->dropIndex(['transfer_flag']);
            $table->dropIndex(['event_type']);
            $table->dropIndex(['department']);
            $table->dropIndex(['status']);
        });
    }
};
