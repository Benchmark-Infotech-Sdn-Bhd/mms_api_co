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
            // Column for tax_id
            $table->string('tax_id')->nullable()->after('service_id');
            // Column for item_id
            $table->string('item_id')->nullable()->after('tax_id');
            // Column for account_id
            $table->string('account_id')->nullable()->after('item_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_items_temp', function (Blueprint $table) {
            //
        });
    }
};
