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
        Schema::create('insurance', function (Blueprint $table) {
            // insurance id column
            $table->id();
            // insurance no of worker from column
            $table->integer('no_of_worker_from')->default(0);
            // insurance no of worker to column
            $table->integer('no_of_worker_to')->default(0);
            // insurance fee per pax column
            $table->float('fee_per_pax')->default(0.0);
            // insurance vendor id column
            $table->bigInteger('vendor_id')->unsigned()->nullable();
            // Foreign key from vendors table
            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('cascade');
            // Column for user id who created the insurance 
            $table->integer('created_by')->default(0);
            // Column for user id who modified the insurance 
            $table->integer('modified_by')->default(0);        
            // vendor created time and modified time columns     
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
        Schema::dropIfExists('insurance');
    }
};
