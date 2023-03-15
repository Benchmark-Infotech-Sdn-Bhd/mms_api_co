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
            // accommodation id column
            $table->id();
            // accommodation name column
            $table->string('name',255)->nullable();
            // accommodation location column
            $table->text('location')->nullable();
            // accommodation maximum pax per unit column
            $table->integer('maximum_pax_per_unit')->default(0);
            // accommodation deposit column
            $table->float('deposit',150)->default(0.0);
            // accommodation rent per month column
            $table->float('rent_per_month',150)->nullable();
            // accommodation vendor_id column
            $table->bigInteger('vendor_id')->unsigned()->nullable();
            // Foreign key from vendors table
            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('cascade'); 
            // Column for user id who created the accommodation 
            $table->integer('created_by')->default(0);
            // Column for user id who modified the accommodation 
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
        Schema::dropIfExists('accommodation');
    }
};
