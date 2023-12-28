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
        Schema::table('xero_items', function (Blueprint $table) {
            // Column for company id
            $table->bigInteger('company_id')->default(0)->unsigned();
            // Foreign key from user table
            $table->foreign('company_id')->references('id')->on('company')->onDelete('cascade');

            // Column for status
            $table->string('status')->nullable()->after('is_purchased');
            // Column for rate
            $table->string('rate')->nullable()->after('status');
            // Column for item_type
            $table->string('item_type')->nullable()->after('rate');
            // Column for product_type
            $table->string('product_type')->nullable()->after('item_type');
            // Column for sku
            $table->string('sku')->nullable()->after('product_type');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('xero_items', function (Blueprint $table) {
            //
        });
    }
};
