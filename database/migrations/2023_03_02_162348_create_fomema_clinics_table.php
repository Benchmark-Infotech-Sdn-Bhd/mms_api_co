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
            $table->id();
            $table->string('clinic_name',255)->nullable();
            $table->string('person_in_charge',255)->nullable();
            $table->string('pic_contact_number',20)->nullable();
            $table->string('address')->nullable();
            $table->string('state',150)->nullable();
            $table->string('city',150)->nullable();
            $table->string('postcode')->nullable();
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
        Schema::dropIfExists('fomema_clinics');
    }
};
