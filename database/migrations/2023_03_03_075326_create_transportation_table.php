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
        Schema::create('transportation', function (Blueprint $table) {
            // transportation id column
            $table->id();
            // transportation driver name column
            $table->string('driver_name',255)->nullable();
            // transportation driver contact number column
            $table->string('driver_contact_number',20)->nullable();
            // transportation driver license number column
            $table->string('driver_license_number',150)->nullable();
            // transportation vehicle type column
            $table->string('vehicle_type',150)->nullable();
            // transportation number plate column
            $table->string('number_plate',20)->nullable();
            // transportation vehicle capacity column
            $table->string('vehicle_capacity',150)->nullable();
            // transportation vendor_id column
            $table->bigInteger('vendor_id')->unsigned()->nullable();
            // Foreign key from vendors table
            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('cascade');
            $table->index(['vendor_id']); 
            // Column for user id who created the transportation 
            $table->integer('created_by')->default(0);
            // Column for user id who modified the transportation 
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
        Schema::dropIfExists('transportation');
    }
};
