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
        Schema::create('direct_recruitment_application_status', function (Blueprint $table) {
            // Column for status ID 
            $table->id();
            // Column for application status name
            $table->string('status_name', 255)->nullable();
            // Column for application status's status
            $table->tinyInteger('status')->default(1);
            // Status created time and modified time columns 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('direct_recruitment_application_status');
    }
};
