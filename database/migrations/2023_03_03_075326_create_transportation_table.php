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
            $table->id();
            $table->string('driver_name',255)->nullable();
            $table->string('driver_contact_number',20)->nullable();
            $table->string('driver_license_number',150)->nullable();
            $table->string('vehicle_type',150)->nullable();
            $table->string('number_plate',20)->nullable();
            $table->string('vehicle_capacity',150)->nullable();
            $table->bigInteger('vendor_id')->unsigned()->nullable();
            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('cascade');
            $table->index(['vendor_id']); 
            $table->integer('created_by')->default(0);
            $table->integer('modified_by')->default(0);            
            $table->timestamps();
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
