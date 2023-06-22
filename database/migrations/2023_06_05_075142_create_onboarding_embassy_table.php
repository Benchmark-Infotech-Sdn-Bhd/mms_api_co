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
        Schema::create('onboarding_embassy', function (Blueprint $table) {
            $table->id();
            // onboarding attestation Id column
            $table->unsignedBigInteger('onboarding_attestation_id');
            // Foreign key from onboarding attestation table
            $table->foreign('onboarding_attestation_id')
              ->references('id')->on('onboarding_attestation')->onDelete('cascade');
            // embassy attestation Id column
            $table->unsignedBigInteger('embassy_attestation_id');
            // Foreign key from embassy attestation table
            $table->foreign('embassy_attestation_id')
              ->references('id')->on('embassy_attestation_file_costing')->onDelete('cascade');
            // colmun file name
            $table->string('file_name', 255);
            // colmun file type
            $table->string('file_type', 255);
            // colmun file url
            $table->text('file_url')->nullable();
            // colmun amount
            $table->float('amount')->default(0.0);
            // Column for user id who created the onboarding embassy
            $table->integer('created_by')->default(0);
              // Column for user id who modified the onboarding embassy
            $table->integer('modified_by')->default(0);
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
        Schema::dropIfExists('onboarding_embassy');
    }
};
