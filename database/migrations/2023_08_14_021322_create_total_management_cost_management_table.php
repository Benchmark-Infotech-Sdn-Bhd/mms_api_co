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
        Schema::create('total_management_cost_management', function (Blueprint $table) {
            // cost management Id column
            $table->id();
            // Column for application id
            $table->bigInteger('application_id')->unsigned();
            // Foreign key from total_management_applications table
            $table->foreign('application_id')->references('id')->on('total_management_applications')->onDelete('cascade');
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
            // Column for user id who created the cost management 
            $table->integer('created_by')->default(0);
            // Column for user id who modified the cost management
            $table->integer('modified_by')->default(0);
            // cost management created time and modified time columns 
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
            Schema::dropIfExists('total_management_cost_management');
        }
    }
};
