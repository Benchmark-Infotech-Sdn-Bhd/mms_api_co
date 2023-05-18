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
        Schema::create('application_summary', function (Blueprint $table) {
            // Application Summary Id column
            $table->id();
            // Direct Recruitment Application Id column
            $table->unsignedBigInteger('application_id');
            // Foreign key from Direct Recruitment Application table
            $table->foreign('application_id')
              ->references('id')->on('directrecruitment_applications')->onDelete('cascade');
            // Application Summary Action column
            $table->string('action',255);
            // Application Summary status column
            $table->string('status',255)->nullable();;
            // Column for user id who created the Application Summary
            $table->integer('created_by')->default(0);
            // Column for user id who modified the Application Summary
            $table->integer('modified_by')->default(0);
            // FWCMS created time and modified time columns 
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
        Schema::dropIfExists('application_summary');
    }
};
