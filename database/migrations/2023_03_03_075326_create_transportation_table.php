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
            $table->string('driver_name')->nullable();
            $table->string('driver_contact_number')->nullable();
            $table->string('driver_license_number')->nullable();
            $table->string('vehicle_type')->nullable();
            $table->string('number_plate')->nullable();
            $table->string('vehicle_capacity')->nullable();
            $table->bigInteger('vendor_id')->unsigned()->nullable();
            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('cascade');
            $table->softDeletes();
            $table->timestamps();
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
