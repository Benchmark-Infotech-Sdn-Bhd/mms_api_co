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
        Schema::create('xero_tax_rates', function (Blueprint $table) {
            //Id column
            $table->id();
            // name column
            $table->string('name', 255)->nullable();
            //tax type column
            $table->string('tax_type', 255)->nullable();
            //report tax type column
            $table->string('report_tax_type', 255)->nullable();
            // can applyto assets column
            $table->string('can_applyto_assets', 10)->nullable();
            // can applyto equity column
            $table->string('can_applyto_equity', 10)->nullable();
            // can applyto expenses column
            $table->string('can_applyto_expenses', 10)->nullable();
            // can applyto liabilities colmun
            $table->string('can_applyto_liabilities', 10)->nullable();
            // can applyto revenue column
            $table->string('can_applyto_revenue', 10)->nullable();
            // display tax rate column
            $table->float('display_tax_rate')->default(0.0);
            // effective rate column
            $table->float('effective_rate')->default(0.0);
            // status column
            $table->string('status', 10)->nullable();
            // Column for user id who created the tax rates
            $table->integer('created_by')->default(0);
            // Column for user id who modified the tax rates
            $table->integer('modified_by')->default(0);
            // tax rate created time and modified time columns
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
        Schema::dropIfExists('xero_tax_rates');
    }
};
