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
        Schema::create('worker_immigration', function (Blueprint $table) {
            // column id
            $table->id();
             // column worker id
             $table->bigInteger('worker_id')->unsigned()->nullable();
             // Foreign key from worker table
             $table->foreign('worker_id')->references('id')->on('workers')->onDelete('cascade');
             // colmun total fee
            $table->float('total_fee')->default(0.0);
            // column reference number
            $table->string('immigration_reference_number', 255);
            // Column for payment date
            $table->date('payment_date');
             // Column for status
            $table->enum('immigration_status',['Pending', 'Paid'])->default('Pending')->index();
            // Column for user id who created the worker immigration 
             $table->integer('created_by')->default(0);
             // Column for user id who modified the worker immigration 
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
        Schema::dropIfExists('worker_immigration');
    }
};
