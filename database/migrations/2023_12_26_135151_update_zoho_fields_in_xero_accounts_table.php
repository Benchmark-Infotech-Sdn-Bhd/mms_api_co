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
        Schema::table('xero_accounts', function (Blueprint $table) {
            
            // Column for company id
            $table->bigInteger('company_id')->default(0)->unsigned();
            // Foreign key from user table
            $table->foreign('company_id')->references('id')->on('company')->onDelete('cascade');

            // Column for description
            $table->text('description')->nullable()->after('reporting_code');
            // Column for tax_specific_type
            $table->string('tax_specific_type')->nullable()->after('description');
            // Column for is_user_created
            $table->string('is_user_created')->nullable()->after('tax_specific_type');
            // Column for is_system_account
            $table->string('is_system_account')->nullable()->after('is_user_created');
            // Column for can_show_in_ze
            $table->string('can_show_in_ze')->nullable()->after('is_system_account');
            // Column for parent_account_id
            $table->string('parent_account_id')->nullable()->after('can_show_in_ze');
            // Column for parent_account_name
            $table->string('parent_account_name')->nullable()->after('parent_account_id');
            // Column for depth
            $table->string('depth')->nullable()->after('parent_account_name');
            // Column for has_attachment
            $table->string('has_attachment')->nullable()->after('depth');
            // Column for is_child_present
            $table->string('is_child_present')->nullable()->after('has_attachment');
            // Column for child_count
            $table->string('child_count')->nullable()->after('is_child_present');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('xero_accounts', function (Blueprint $table) {
            //
        });
    }
};
