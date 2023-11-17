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
        Schema::table('worker_bulk_upload', function (Blueprint $table) {
            // Column for company id
            $table->bigInteger('company_id')->unsigned();
            // Foreign key from user table
            $table->foreign('company_id')->references('id')->on('company')->onDelete('cascade');
            // Column for actual row count
            $table->integer('actual_row_count')->default(0)->after('total_failure');
            // Column for process status
            $table->string('process_status')->nullable()->after('actual_row_count');
            // Column for failure case url
            $table->text('failure_case_url')->nullable()->after('process_status');
             
             
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
