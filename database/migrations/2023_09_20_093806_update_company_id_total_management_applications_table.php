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
        Schema::table('total_management_applications', function (Blueprint $table) {
            if (DB::getDriverName() === 'sqlite') {
                // Column for company id
                $table->bigInteger('company_id')->default(0)->unsigned();
            } else {
                // Column for company id
                $table->bigInteger('company_id')->unsigned();
            }

            // Foreign key from user table
            $table->foreign('company_id')->references('id')->on('company')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
