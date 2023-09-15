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
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            // Invoice column
            $table->bigInteger('invoice_id')->unsigned();
            // Foreign key form crm_prospects table
            $table->foreign('invoice_id')->references('id')->on('invoice')->onDelete('cascade');
            // Item column
            $table->string('item', 255)->nullable();
            // Description column
            $table->text('description')->nullable();
            // Quantity column
            $table->integer('quantity');
            // Price column
            $table->integer('price');
            // Total price column
            $table->integer('total_price');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};
