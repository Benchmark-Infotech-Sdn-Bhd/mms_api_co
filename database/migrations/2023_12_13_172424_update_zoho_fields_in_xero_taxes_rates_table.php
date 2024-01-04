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
        Schema::table('xero_tax_rates', function (Blueprint $table) {
            // Column for company id
            $table->bigInteger('company_id')->default(0)->unsigned();
            // Foreign key from user table
            $table->foreign('company_id')->references('id')->on('company')->onDelete('cascade');

            // Column for tax_id
            $table->string('tax_id')->nullable()->after('status');
            // Column for tax_specific_type
            $table->string('tax_specific_type')->nullable()->after('tax_id');
            // Column for output_tax_account_name
            $table->string('output_tax_account_name')->nullable()->after('tax_specific_type');
            // Column for purchase_tax_account_name
            $table->string('purchase_tax_account_name')->nullable()->after('output_tax_account_name');
            // Column for tax_account_id
            $table->string('tax_account_id')->nullable()->after('purchase_tax_account_name');
            // Column for purchase_tax_account_id
            $table->string('purchase_tax_account_id')->nullable()->after('tax_account_id');
            // Column for is_inactive
            $table->string('is_inactive')->nullable()->after('purchase_tax_account_id');
            // Column for is_value_added
            $table->string('is_value_added')->nullable()->after('is_inactive');
            // Column for is_default_tax
            $table->string('is_default_tax')->nullable()->after('is_value_added');
            // Column for is_editable
            $table->string('is_editable')->nullable()->after('is_default_tax');
            // Column for last_modified_time
            $table->string('last_modified_time')->nullable()->after('is_editable');            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('xero_taxes_rates', function (Blueprint $table) {
            //
        });
    }
};
