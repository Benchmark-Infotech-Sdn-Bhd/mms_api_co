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
        Schema::create('fomema_clinics', function (Blueprint $table) {
            // fomema clinics id column
            $table->id();
            // fomema clinics clinic name column
            $table->string('clinic_name',255)->nullable();
            // fomema clinics person in charge column
            $table->string('person_in_charge',255)->nullable();
            // fomema clinics pic contact number column
            $table->string('pic_contact_number',20)->nullable();
            // fomema clinics address column
            $table->string('address')->nullable();
            // fomema clinics state column
            $table->string('state',150)->nullable();
            // fomema clinics city column
            $table->string('city',150)->nullable();
            // fomema clinics postcode column
            $table->string('postcode')->nullable();
            // Column for user id who created the fomema clinics 
            $table->integer('created_by')->default(0);
            // Column for user id who modified the fomema clinics 
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
        Schema::dropIfExists('fomema_clinics');
    }
};
