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
        Schema::create('fee_registration_sectors', function (Blueprint $table) {
            // fee registration sectors id column
            $table->id();
            // fee registration sectors fee registration id column
            $table->bigInteger('fee_reg_id')->unsigned()->nullable();
            // Foreign key from fee registration table
            $table->foreign('fee_reg_id')->references('id')->on('fee_registration')->onDelete('cascade');
            // fee registration sectors sector id column
            $table->integer('sector_id')->nullable()->unsigned()->index();
            // fee registration sectors sector name column
            $table->string('sector_name', 255);
            // fee registration sectors sub sectors name column
            $table->string('sub_sector_name', 255);
            // fee registration sectors checklist status column
            $table->string('checklist_status')->nullable();
            // fee registration sectors created time and modified time columns 
            $table->timestamps();
            // for soft delete
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fee_registration_sectors');
    }
};
