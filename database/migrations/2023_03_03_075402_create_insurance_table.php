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
        Schema::create('insurance', function (Blueprint $table) {
            $table->id();
            $table->integer('no_of_worker_from')->default(0);
            $table->integer('no_of_worker_to')->default(0);
            $table->float('fee_per_pax')->default(0.0);
            $table->bigInteger('vendor_id')->unsigned()->nullable();
            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('cascade');
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
        Schema::dropIfExists('insurance');
    }
};
