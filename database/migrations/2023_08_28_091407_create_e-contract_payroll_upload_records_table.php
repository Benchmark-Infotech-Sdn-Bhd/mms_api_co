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
        Schema::create('e-contract_payroll_upload_records', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('bulk_upload_id')->unsigned();
            $table->foreign('bulk_upload_id')->references('id')->on('e-contract_payroll_bulk_upload');
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
        if (DB::getDriverName() !== 'sqlite') {
            Schema::dropIfExists('e-contract_payroll_upload_records');
        }
    }
};
