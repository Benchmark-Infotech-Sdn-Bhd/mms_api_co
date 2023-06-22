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
        Schema::create('worker_post_arrival', function (Blueprint $table) {
            // worker post arrival id
            $table->id();
            // column worker id
            $table->bigInteger('worker_id')->unsigned()->nullable();
            // Foreign key from worker table
            $table->foreign('worker_id')->references('id')->on('workers')->onDelete('cascade');
            // column arrival id
            $table->bigInteger('arrival_id')->unsigned()->nullable();
            // Foreign key from arrival table
            $table->foreign('arrival_id')->references('id')->on('arrival')->onDelete('cascade');
            // Column for post arrival status
            $table->enum('status',['Not Arrived', 'Arrived', 'JTK Report', 'Cancelled', 'Postponed'])->default('Not Arrived');
            // Column for arrived date
            $table->date('arrived_date')->nullable();
            // Column for entry visa valid until date
            $table->date('entry_visa_valid_until')->nullable();
            // Column for JTK Report submitted on
            $table->date('jtk_submitted_on')->nullable();
            // Column for new arrival date if postponed
            $table->date('new_arrival_date')->nullable();
            // Coulmn for flight number
            $table->string('flight_number')->nullable();
            // Column for arrival time
            $table->time('arrival_time')->nullable();
            // Column for remarks
            $table->text('remarks')->nullable();
            // Column for user id who created the post arrival 
            $table->integer('created_by')->default(0);
            // Column for user id who modified the post arrival 
            $table->integer('modified_by')->default(0);
            // Column for user id who created and modified the post arrival 
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
        Schema::dropIfExists('worker_post_arrival');
    }
};
