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
        Schema::create('embassy_attestation_file_costing', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('country_id');
            $table->text('title');
            $table->float('amount')->default(0);
            $table->integer('created_by')->default(0);
            $table->integer('modified_by')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('country_id')
              ->references('id')->on('countries')->onDelete('cascade');
            $table->index(['country_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('embassy_attestation_file_costing');
    }
};
