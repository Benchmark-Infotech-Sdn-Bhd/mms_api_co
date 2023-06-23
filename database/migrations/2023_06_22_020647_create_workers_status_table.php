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
        Schema::create('worker_status', function (Blueprint $table) {
            // Column for Id 
            $table->id();
            // Country Id column
            $table->unsignedBigInteger('onboarding_country_id');
            // Foreign key from directrecruitment onboarding countries table
            $table->foreign('onboarding_country_id')
              ->references('id')->on('directrecruitment_onboarding_countries')->onDelete('cascade');
            // Direct Recruitment Agent Id column
            $table->unsignedBigInteger('agent_id')->nullable();
            
            // Direct Recruitment Application Id column
            $table->unsignedBigInteger('application_id');
            // Foreign key from Direct Recruitment Application table
            $table->foreign('application_id')
              ->references('id')->on('directrecruitment_applications')->onDelete('cascade');
            // Column for worker status item name
            $table->string('item', 255)->nullable();
            // Column for worker updated time
            $table->date('updated_on')->nullable();
            // Column for status
            $table->integer('status')->default(1)->index();
            // Column for user id who created the calling visa status
            $table->integer('created_by')->default(0);
            // Column for user id who modified the calling visa status
            $table->integer('modified_by')->default(0);
            // calling worker created time and modified time columns 
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
        Schema::dropIfExists('workers_status');
    }
};
