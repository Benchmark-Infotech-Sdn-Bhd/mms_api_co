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
        Schema::create('fee_registration_services', function (Blueprint $table) {
            // fee registration services id column
            $table->id();
            // fee registration services fee registration id column
            $table->bigInteger('fee_reg_id')->unsigned()->nullable();
            // Foreign key from fee registration table
            $table->foreign('fee_reg_id')->references('id')->on('fee_registration')->onDelete('cascade');
            // fee registration services service id column
            $table->integer('service_id')->nullable()->unsigned()->index();
            // fee registration services service name column
            $table->string('service_name', 255);
            // fee registration services status column
            $table->tinyInteger('status')->default(0)->unsigned()->index();
            // fee registration services created time and modified time columns 
            $table->timestamps();
            // // for soft delete
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fee_registration_services');
    }
};
