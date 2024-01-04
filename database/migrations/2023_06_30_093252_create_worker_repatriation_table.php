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
        Schema::create('worker_repatriation', function (Blueprint $table) {
            // Column for worker repatriation id
            $table->id();
            // worker id column
            $table->bigInteger('worker_id')->unsigned()->nullable();
            // Foreign key from Worker table
            $table->foreign('worker_id')->references('id')->on('workers')->onDelete('cascade');
            // Column for flight number
            $table->string('flight_number', 255)->nullable();
            // Column for flight date
            $table->date('flight_date')->nullable();
            // Column for expenses
            $table->integer('expenses')->nullable();
            // Column for check out memo reference number
            $table->integer('checkout_memo_reference_number')->nullable();
            // Column for user id who created worker repatriation
            $table->integer('created_by')->default(0);
            // Column for user id who modified worker repatriation
            $table->integer('modified_by')->default(0);
            // Column for worker repatriation created and modified time
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
        Schema::dropIfExists('worker_repatriation');
    }
};
