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
        Schema::create('access_control_url', function (Blueprint $table) {
            // Column for Id
            $table->id();
            // Column for module id from modules table
            $table->string('module_id', 255)->nullable();
            // Column for module name
            $table->string('module_name', 255)->nullable();
            // Column for module url
            $table->string('url')->nullable();
            // Column for status
            $table->tinyInteger('status')->default(1);
            // Column for created and update time
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('access_control_url');
    }
};
