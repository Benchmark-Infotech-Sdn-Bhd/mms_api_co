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
            $table->id();
            $table->string('item_name',255)->nullable();
            $table->string('cost')->nullable();
            $table->string('fee_type',150)->nullable();
            $table->string('applicable_for')->nullable();
            $table->string('sectors',150)->nullable();
            $table->integer('created_by')->default(0);
            $table->integer('modified_by')->default(0);            
            $table->timestamps();
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
