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
        Schema::create('vendors', function (Blueprint $table) {
            // vendors id column
            $table->id();
            // vendor name column
            $table->string('name',255)->nullable();
            // vendor type column
            $table->string('type',150)->nullable();
            // vendor email address column
            $table->string('email_address',150)->nullable();
            // vendor contact number column
            $table->string('contact_number',20)->nullable();
            // vendor person in charge column
            $table->string('person_in_charge',255)->nullable();
            // vendor pic contact number column
            $table->string('pic_contact_number',255)->nullable();
            // vendor address column
            $table->string('address',255)->nullable();
            // Permission state column
            $table->string('state',150)->nullable();
            // vendor city column
            $table->string('city',150)->nullable();
            // vendor postcode column
            $table->string('postcode',20)->nullable();
            // vendor remarks column
            $table->string('remarks',150)->nullable();
            // Column for user id who created the vendor 
            $table->integer('created_by')->default(0);
            // Column for user id who modified the vendor 
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
        Schema::dropIfExists('vendors');
    }
};
