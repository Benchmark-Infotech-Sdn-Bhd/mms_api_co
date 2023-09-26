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
        Schema::table('invoice_items_temp', function (Blueprint $table) {
            // Column for user id who created the expenses
            $table->integer('created_by')->default(0);
            // Column for user id who modified the expenses
            $table->integer('modified_by')->default(0);
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
