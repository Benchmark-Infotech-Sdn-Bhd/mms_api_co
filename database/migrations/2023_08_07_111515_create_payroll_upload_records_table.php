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
        Schema::create('payroll_upload_records', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('bulk_upload_id')->unsigned();
            $table->foreign('bulk_upload_id')->references('id')->on('payroll_bulk_upload');
            $table->longText('parameter')->nullable();
            $table->longText('comments')->nullable();
            $table->integer('status')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_upload_records');
    }
};
