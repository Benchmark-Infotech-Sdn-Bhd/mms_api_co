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
            // Sectors Id column
            $table->id();
            // Sectors name column
            $table->string('sector_name',255);
            // Sectors sub-sector name column
            $table->string('sub_sector_name',255)->nullable();
            // Sectors checklist status column
            $table->enum('checklist_status',['Pending','Done'])->default('Pending');
            // Column for user Id, who created the sectors
            $table->integer('created_by')->default(0);
            // Column for user Id, who modified the sectors
            $table->integer('modified_by')->default(0);
            // Sectors created_at and updated_at columns
            $table->timestamps();
            // softdelete for Sectors
            $table->softDeletes();
            // Indexing for Sectors based on Id, Sector name, checklist status columns
            $table->index(['id','sector_name','checklist_status']);
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
