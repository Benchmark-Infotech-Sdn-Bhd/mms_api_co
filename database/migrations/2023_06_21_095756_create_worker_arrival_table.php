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
        Schema::create('worker_arrival', function (Blueprint $table) {
            // column id
            $table->id();
            // column arrival id
            $table->bigInteger('arrival_id')->unsigned()->nullable();
            // Foreign key from arrival table
            $table->foreign('arrival_id')->references('id')->on('directrecruitment_arrival')->onDelete('cascade');
             // column worker id
             $table->bigInteger('worker_id')->unsigned()->nullable();
             // Foreign key from worker table
             $table->foreign('worker_id')->references('id')->on('workers')->onDelete('cascade');
             // Column for status
             $table->enum('arrival_status',['Not Arrived', 'Arrived', 'Postponed', 'Cancelled'])->default('Not Arrived')->index();
            // Column for user id who created the arrival 
             $table->integer('created_by')->default(0);
             // Column for user id who modified the arrival 
             $table->integer('modified_by')->default(0);
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
        Schema::dropIfExists('worker_arrival');
    }
};
