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
        Schema::create('invoice_items_temp', function (Blueprint $table) {
            $table->id();
            // Service Id column
            $table->bigInteger('service_id')->unsigned();
            // Foreign key form services table
            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');
            // Expense Id column
            $table->bigInteger('expense_id');
            // Invoice Number Column
            $table->string('invoice_number', 255)->nullable();
            // Item column
            $table->string('item', 255)->nullable();
            // Description column
            $table->text('description');
            // Quantity column
            $table->integer('quantity');
            // Price column
            $table->integer('price');
            // Account column
            $table->string('account', 255)->nullable();
            // Tax Rate column
            $table->string('tax_rate', 255)->nullable();
            // Total price column
            $table->integer('total_price');
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
        if (DB::getDriverName() !== 'sqlite') {
            Schema::dropIfExists('invoice_items_temp');
        }
    }
};
