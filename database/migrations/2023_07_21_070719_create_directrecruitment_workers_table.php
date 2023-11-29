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
        Schema::create('directrecruitment_workers', function (Blueprint $table) {
            $table->id();
            // workerid column
            $table->bigInteger('worker_id')->unsigned()->nullable();
            // Foreign key from Worker table
            $table->foreign('worker_id')->references('id')->on('workers')->onDelete('cascade');
            // Country Id column
            $table->unsignedBigInteger('onboarding_country_id');
            // Foreign key from directrecruitment onboarding countries table
            $table->foreign('onboarding_country_id')
              ->references('id')->on('directrecruitment_onboarding_countries')->onDelete('cascade');
            // Direct Recruitment Agent Id column
            $table->unsignedBigInteger('agent_id');
            // Foreign key from Agent table
            $table->foreign('agent_id')
              ->references('id')->on('directrecruitment_onboarding_agent')->onDelete('cascade');
            // Direct Recruitment Application Id column
            $table->unsignedBigInteger('application_id');
            // Foreign key from Direct Recruitment Application table
            $table->foreign('application_id')
              ->references('id')->on('directrecruitment_applications')->onDelete('cascade');
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
      if (DB::getDriverName() !== 'sqlite') {
        Schema::dropIfExists('directrecruitment_workers');
      }
    }
};
