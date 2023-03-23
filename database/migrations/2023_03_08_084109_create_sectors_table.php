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
        Schema::create('sectors', function (Blueprint $table) {
            $table->id();
            $table->string('sector_name',255);
            $table->string('sub_sector_name',255)->nullable();
            $table->enum('checklist_status',['Pending','Done'])->default('Pending');
            $table->integer('created_by')->default(0);
            $table->integer('modified_by')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['sector_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sectors');
    }
};
