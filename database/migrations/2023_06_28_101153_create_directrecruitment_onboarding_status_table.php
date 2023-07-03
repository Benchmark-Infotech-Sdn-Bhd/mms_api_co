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
        Schema::create('directrecruitment_onboarding_status', function (Blueprint $table) {
            // Column for Id 
            $table->id();
            // Column for Worker name
            $table->string('name', 255);
            // Column for Worker status
            $table->tinyInteger('status')->default(1);
            // Column for user id who created the Worker
            $table->integer('created_by')->default(0);
            // Column for user id who modified the Worker
            $table->integer('modified_by')->default(0);
            // Worker created time and modified time columns 
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
        Schema::dropIfExists('directrecruitment_onboarding_status');
    }
};
