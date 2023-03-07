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
        Schema::create('accommodation', function (Blueprint $table) {
            $table->id();
            $table->string('accommodation_name')->nullable();
            $table->string('number_of_units')->nullable();
            $table->string('number_of_rooms')->nullable();
            $table->string('maximum_pax_per_room')->nullable();
            $table->string('cost_per_pax')->nullable();
            $table->string('attachment')->nullable();
            $table->string('rent_deposit')->nullable();
            $table->string('rent_per_month')->nullable();
            $table->string('rent_advance')->nullable();  
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
        Schema::dropIfExists('accommodation');
    }
};
