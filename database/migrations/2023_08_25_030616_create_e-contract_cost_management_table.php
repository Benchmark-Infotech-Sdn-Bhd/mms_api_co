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
        Schema::create('e-contract_cost_management', function (Blueprint $table) {
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
            // invoice id Column
            $table->bigInteger('invoice_id')->default(0);
            // invoice status Column
            $table->enum('invoice_status',['Not Generated','Generated'])->default('Not Generated');
            // Column for user id who created the e-contract_cost_management
            $table->integer('created_by')->default(0);
            // Column for user id who modified the e-contract_cost_management
            $table->integer('modified_by')->default(0);
            // e-contract_cost_management created time and modified time columns 
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
        if (DB::getDriverName() !== 'sqlite') {
            Schema::dropIfExists('e-contract_cost_management');
        }
    }
};
