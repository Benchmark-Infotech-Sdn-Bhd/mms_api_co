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
            // Expenses Id column
            $table->id();
            // Column for application id
            $table->bigInteger('application_id')->unsigned();
            // Foreign key from e-contract_applications table
            $table->foreign('application_id')->references('id')->on('e-contract_applications')->onDelete('cascade');
            // Title column
            $table->string('title', 255);
            // Type Column
            $table->string('type', 255)->nullable();
            // Payment reference number column
            $table->string('payment_reference_number', 255);
            // Quantity column
            $table->integer('quantity');
            // Amount column
            $table->decimal('amount', 8,2)->default(0);
            // Deduction column
            $table->decimal('deduction', 8,2)->default(0);
            // Payment Date column
            $table->date('payment_date');
            // Remarks column
            $table->text('remarks')->nullable();
            // Column for user id who created the expenses
            $table->integer('created_by')->default(0);
            // Column for user id who modified the expenses
            $table->integer('modified_by')->default(0);
            // expenses created time and modified time columns
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
