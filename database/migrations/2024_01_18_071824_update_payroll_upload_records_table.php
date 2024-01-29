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
        Schema::table('payroll_upload_records', function (Blueprint $table) {
            // Column for success flag
            $table->tinyInteger('success_flag')->default(0)->after('status');
            if (DB::getDriverName() === 'sqlite') {
                // Column for company id
                $table->bigInteger('company_id')->default(0)->unsigned()->after('success_flag');
            } else {
                // Column for company id
                $table->bigInteger('company_id')->unsigned()->after('success_flag');
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
