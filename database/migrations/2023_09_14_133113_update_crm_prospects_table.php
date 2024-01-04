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
        Schema::table('crm_prospects', function (Blueprint $table) {
            
            //Financial details
            $table->string('bank_account_name', 255)->nullable();
            $table->bigInteger('bank_account_number')->default(0);
            $table->string('tax_id', 255)->default(0);
            $table->text('account_receivable_tax_type')->nullable();
            $table->text('account_payable_tax_type')->nullable();
            $table->text('xero_contact_id')->nullable();
            
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
