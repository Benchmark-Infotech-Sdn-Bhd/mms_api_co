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
        Schema::table('workers', function (Blueprint $table) {
            // Column for calling visa cancellation remarks - change to default nullable
            $table->text('remarks')->nullable()->change();
            // Column for Replace worker id - To track the worker replaced for this worker
            $table->integer('replace_worker_id')->nullable()->after('remarks');
            // Column for replace done by
            $table->integer('replace_by')->nullable()->after('replace_worker_id');
            // Column for replace at - date and time 
            $table->dateTime('replace_at')->nullable()->after('replace_by');
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
