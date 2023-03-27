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
        Schema::create('services', function (Blueprint $table) {

            // Service id column
            $table->id();

            // Service name column
            $table->string('service_name',255);

            // Service status column
            $table->tinyInteger('status')->default(1)->unsigned()->index();

            // Service created time and modified time columns
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service');
    }
};
