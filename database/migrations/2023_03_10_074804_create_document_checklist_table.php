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
        Schema::create('document_checklist', function (Blueprint $table) {
            // DocumentChecklist Id column
            $table->id();
            // DocumentChecklist Sector Id column
            $table->unsignedBigInteger('sector_id');
            // DocumentChecklist Document title column
            $table->text('document_title');
            // Column for user Id, who created the DocumentChecklist
            $table->integer('created_by')->default(0);
            // Column for user Id, who modified the DocumentChecklist
            $table->integer('modified_by')->default(0);
            // DocumentChecklist created_at and updated_at columns
            $table->timestamps();
            // softdelete for DocumentChecklist
            $table->softDeletes();
            // Foreign key from sectors table
            $table->foreign('sector_id')
              ->references('id')->on('sectors')->onDelete('cascade');
            // Indexing for DocumentChecklist based on Id, Sector Id columns
            $table->index(['id','sector_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_checklist');
    }
};
