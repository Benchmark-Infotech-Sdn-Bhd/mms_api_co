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
            $table->id();
            $table->unsignedBigInteger('sector_id');
            $table->text('document_title');
            $table->integer('created_by')->default(0);
            $table->integer('modified_by')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('sector_id')
              ->references('id')->on('sectors')->onDelete('cascade');
            $table->index(['sector_id']);
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
