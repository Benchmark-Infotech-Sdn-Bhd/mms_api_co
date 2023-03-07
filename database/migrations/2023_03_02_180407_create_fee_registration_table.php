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
            $table->string('item')->nullable();
            $table->string('fee_per_pax')->nullable();
            $table->string('type')->nullable();
            $table->string('applicable_for')->nullable();
            $table->string('sectors')->nullable();
            $table->softDeletes();
            $table->timestamps();
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
