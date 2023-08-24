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
        Schema::create('e-contract_expenses', function (Blueprint $table) {
            // Id column
            $table->id();
            // Column for project id
            $table->bigInteger('project_id')->unsigned();
            // Foreign key from e-contract_project table
            $table->foreign('project_id')->references('id')->on('e-contract_project')->onDelete('cascade');
            // Title column
            $table->string('title', 255);
            // Payment reference number column
            $table->string('payment_reference_number', 255);
            // Quantity column
            $table->integer('quantity');
            // Amount column
            $table->decimal('amount', 8, 2)->default(0);
            // Payment Date column
            $table->date('payment_date');
            // Remarks column
            $table->text('remarks')->nullable();
            // Column for user id who created the e-contract_expenses 
            $table->integer('created_by')->default(0);
            // Column for user id who modified the e-contract_expenses
            $table->integer('modified_by')->default(0);
            // e-contract_expenses created time and modified time columns 
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
        Schema::dropIfExists('e-contract_expenses');
    }
};
