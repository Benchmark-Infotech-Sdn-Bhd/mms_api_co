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
        Schema::create('fee_registration', function (Blueprint $table) {
            // fee registration id column
            $table->id();
            // fee registration item name column
            $table->string('item_name',255)->nullable();
            // fee registration cost column
            $table->float('cost')->default(0.0);
            // fee registration fee type column
            $table->string('fee_type',150)->nullable();
            // fee registration applicable for column
            $table->string('applicable_for')->nullable();
            // fee registration sectors column
            $table->string('sectors',150)->nullable();
            // Column for user id who created the fee registration 
            $table->integer('created_by')->default(0);
            // Column for user id who modified the fee registration 
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
        Schema::dropIfExists('fee_registration');
    }
};
