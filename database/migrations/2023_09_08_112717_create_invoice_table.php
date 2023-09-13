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
        Schema::create('invoice', function (Blueprint $table) {
            // Invoice Id column
            $table->id();
            // CRM prospect(company) id column
            $table->bigInteger('crm_prospect_id')->unsigned();
            // Foreign key form crm_prospects table
            $table->foreign('crm_prospect_id')->references('id')->on('crm_prospects')->onDelete('cascade');
            // Issue Date column
            $table->date('issue_date');
            // Due Date column
            $table->date('due_date');
            // Reference Number column
            $table->string('reference_number', 255)->nullable();
            // Account column
            $table->string('account', 255)->nullable();
            // Tax column
            $table->decimal('tax', 4,2)->default(0);
            // Ammount column
            $table->decimal('amount', 8,2)->default(0);
            // Column for user id who created the expenses
            $table->integer('created_by')->default(0);
            // Column for user id who modified the expenses
            $table->integer('modified_by')->default(0);
            // vendor created time and modified time columns        
            $table->timestamps();
            // for soft delete
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice');
    }
};
