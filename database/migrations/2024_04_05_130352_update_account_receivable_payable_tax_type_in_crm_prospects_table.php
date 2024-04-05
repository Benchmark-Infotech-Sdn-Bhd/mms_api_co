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
            $table->integer('account_receivable_tax_type')->nullable()->change();
            $table->integer('account_payable_tax_type')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('crm_prospects', function (Blueprint $table) {
            //
        });
    }
};
