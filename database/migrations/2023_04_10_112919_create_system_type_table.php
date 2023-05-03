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
        Schema::create('system_type', function (Blueprint $table) {
            // System type id column
            $table->id();
            // System name column
            $table->string('system_name', 255);
            // System type status column
            $table->tinyInteger('status')->default(1)->unsigned()->index();
            // System type created time and modified time columns
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_type');
    }
};
