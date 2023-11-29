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
        Schema::create('worker_event', function (Blueprint $table) {
            // column for id
            $table->id();
            // column worker id
            $table->bigInteger('worker_id')->unsigned()->nullable();
            // Foreign key from worker table
            $table->foreign('worker_id')->references('id')->on('workers')->onDelete('cascade');
            // Column for event date 
            $table->date('event_date')->nullable();
            // Column for event type
            $table->enum('event_type',['Counselling', 'Repatriated', 'e-Run', 'Deceased'])->nullable();
            // Column for flight number
            $table->string('flight_number')->nullable();
            // Column for Departure date 
            $table->date('departure_date')->nullable();
            // Column for remarks
            $table->string('remarks',255)->nullable();
            // Column for user id who created the event 
            $table->integer('created_by')->default(0);
            // Column for user id who modified the event 
            $table->integer('modified_by')->default(0);
            // Column for event created and modified time
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
        if (DB::getDriverName() !== 'sqlite') {
            Schema::dropIfExists('worker_event');
        }
    }
};
