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
            $table->string('name',255)->nullable();
            $table->integer('maximum_pax_per_unit')->default(0);
            $table->float('deposit',150)->default(0.0);
            $table->float('rent_per_month',150)->nullable();
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
        Schema::dropIfExists('accommodation');
    }
};
