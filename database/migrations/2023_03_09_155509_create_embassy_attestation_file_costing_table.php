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
            // EmbassyAttestationFileCosting Id column
            $table->id();
            // EmbassyAttestationFileCosting Country Id column
            $table->unsignedBigInteger('country_id');
            // EmbassyAttestationFileCosting document title column
            $table->text('title');
            // EmbassyAttestationFileCosting amount column
            $table->float('amount')->default(0);
            // Column for user Id, who created the EmbassyAttestationFileCosting
            $table->integer('created_by')->default(0);
            // Column for user Id, who modified the EmbassyAttestationFileCosting
            $table->integer('modified_by')->default(0);
            // EmbassyAttestationFileCosting created_at and updated_at columns
            $table->timestamps();
            // softdelete for EmbassyAttestationFileCosting
            $table->softDeletes();
            // Foreign key from countries table
            $table->foreign('country_id')
              ->references('id')->on('countries')->onDelete('cascade');
            // Indexing for EmbassyAttestationFileCosting based on Id, Country Id columns
            $table->index(['id','country_id']);
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
