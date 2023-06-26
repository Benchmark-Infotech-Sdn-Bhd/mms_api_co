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
        Schema::create('workers', function (Blueprint $table) {
            // Column for Id 
            $table->id();
            // Country Id column
            $table->unsignedBigInteger('onboarding_country_id');
            // Foreign key from directrecruitment onboarding countries table
            $table->foreign('onboarding_country_id')
              ->references('id')->on('directrecruitment_onboarding_countries')->onDelete('cascade');
            // Direct Recruitment Agent Id column
            $table->unsignedBigInteger('agent_id');
            // Foreign key from Agent table
            $table->foreign('agent_id')
              ->references('id')->on('agent')->onDelete('cascade');
            // Direct Recruitment Application Id column
            $table->unsignedBigInteger('application_id');
            // Foreign key from Direct Recruitment Application table
            $table->foreign('application_id')
              ->references('id')->on('directrecruitment_applications')->onDelete('cascade');
            // Column for Worker name
            $table->string('name', 255);
            // Worker Gender column
            $table->string('gender',15);
            // Worker Date of birth column
            $table->date('date_of_birth');
            // Worker Passport number column
            $table->string('passport_number')->index();
            // Worker Passport validity date column
            $table->date('passport_valid_until');
            // Worker FOMEMA validity date column
            $table->date('fomema_valid_until')->nullable();
            // Worker Address column
            $table->text('address');
            // Worker city column
            $table->string('city',150)->nullable();
            // Worker state column
            $table->string('state',150);
            // Column for Worker status
            $table->tinyInteger('status')->default(1);
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
        Schema::dropIfExists('workers');
    }
};
