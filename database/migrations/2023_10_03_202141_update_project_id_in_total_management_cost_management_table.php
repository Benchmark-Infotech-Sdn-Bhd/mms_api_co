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
        Schema::table('total_management_cost_management', function (Blueprint $table) {
            // Column for project id
            $table->bigInteger('project_id')->unsigned();
            // Foreign key from total_management_project table
            $table->foreign('project_id')->references('id')->on('total_management_project')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('total_management_cost_management', function (Blueprint $table) {
            //
        });
    }
};
