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
        Schema::create('xero_accounts', function (Blueprint $table) {
            //Id column
            $table->id();
            // account_id column
            $table->string('account_id', 255)->nullable();
            //code column
            $table->string('code', 255)->nullable();
            // name column
            $table->string('name', 255)->nullable();
            // status column
            $table->string('status', 10)->nullable();
            // type column
            $table->string('type', 255)->nullable();
            // tax_type column
            $table->string('tax_type', 255)->nullable();
            // class column
            $table->string('class', 255)->nullable();
            // enable_payments_to_account column
            $table->tinyInteger('enable_payments_to_account')->nullable();
            // show_in_expense_claims column
            $table->tinyInteger('show_in_expense_claims')->nullable();
            // bank_account_number column
            $table->string('bank_account_number', 255)->nullable();
            // bank_account_type column
            $table->string('bank_account_type', 255)->nullable();
            // currency_code column
            $table->string('currency_code', 255)->nullable();
            // reporting_code column
            $table->string('reporting_code', 255)->nullable();
            // reporting_code_name column
            $table->string('reporting_code_name', 255)->nullable();
            // Column for user id who created the accounts
            $table->integer('created_by')->default(0);
            // Column for user id who modified the accounts
            $table->integer('modified_by')->default(0);
            // accounts created time and modified time columns
            $table->timestamps();
            // for softdelete
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('xero_accounts');
    }
};
