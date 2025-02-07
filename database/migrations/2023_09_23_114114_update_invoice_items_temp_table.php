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
            $table->decimal('price', 8,2)->default(0)->change();
            $table->decimal('tax_rate', 8,2)->default(0)->change();
            $table->decimal('total_price', 8,2)->default(0)->change();
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
