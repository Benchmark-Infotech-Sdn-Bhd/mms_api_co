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
        Schema::create('branch', function (Blueprint $table) {
            $table->id();
            $table->string('branch_name',255);
            $table->string('state',150);
            $table->string('city',150);
            $table->text('branch_address');
            $table->mediumInteger('postcode');
            $table->unsignedBigInteger('service_id');
            $table->text('remarks')->nullable();
            $table->integer('created_by')->default(0);
            $table->integer('modified_by')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('service_id')
              ->references('id')->on('services')->onDelete('cascade');
            $table->index(['service_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branch');
    }
};
