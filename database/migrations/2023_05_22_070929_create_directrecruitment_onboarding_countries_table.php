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
        Schema::create('directrecruitment_onboarding_countries', function (Blueprint $table) {
            // Onboarding id column
            $table->id();
            // Direct Recruitment Application Id column
            $table->unsignedBigInteger('application_id');
            // Foreign key from Direct Recruitment Application table
            $table->foreign('application_id')
              ->references('id')->on('directrecruitment_applications')->onDelete('cascade');
            // Country Id column
            $table->unsignedBigInteger('country_id');
            // Foreign key from Contries table
            $table->foreign('country_id')
              ->references('id')->on('countries')->onDelete('cascade');
            // Quota column
            $table->integer('quota')->default(0);
            // Utilised Quota column
            $table->integer('utilised_quota')->default(0);
            // Status column
            $table->tinyInteger('status')->default(1)->unsigned();
            // Column for user id who created the Direct recruitment onboarding countries
            $table->integer('created_by')->default(0);
            // Column for user id who modified the Direct recruitment onboarding countries
            $table->integer('modified_by')->default(0);
            // Onboarding created time and modified time columns 
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
      if (DB::getDriverName() !== 'sqlite') {
        Schema::dropIfExists('directrecruitment_onboarding_countries');
      }
    }
};
