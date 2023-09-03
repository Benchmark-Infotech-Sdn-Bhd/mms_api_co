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
        Schema::table('total_management_expenses', function (Blueprint $table) {
            $table->dropColumn('quantity');
            // Column for worker_id
            $table->bigInteger('worker_id')->unsigned()->after('id')->change();
            // Column for application id
            $table->bigInteger('application_id')->unsigned();
            // Foreign key from total_management_applications table
            $table->foreign('application_id')->references('id')->on('total_management_applications')->onDelete('cascade');
            // Column for project id
            $table->bigInteger('project_id')->unsigned();
            // Foreign key from total_management_project table
            $table->foreign('project_id')->references('id')->on('total_management_project')->onDelete('cascade');
            // Column for Type
            $table->string('type', 255)->nullable()->after('title')->change();
            // Paid amount_paid column
            $table->decimal('amount_paid', 8,2)->default(0)->after('amount');
            // Column for Deduction
            $table->decimal('deduction', 8,2)->default(0)->after('amount_paid')->change();
            // Column for remaining amount
            $table->decimal('remaining_amount', 8,2)->default(0)->after('deduction');
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
