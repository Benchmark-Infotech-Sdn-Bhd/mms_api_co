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
        Schema::create('company_module_permission', function (Blueprint $table) {
            $table->id();

            // Column for company id
            $table->bigInteger('company_id')->unsigned();

            // Foreign key from company table
            $table->foreign('company_id')->references('id')->on('company')->onDelete('cascade');

            // Column for module id
            $table->bigInteger('module_id')->unsigned();

            // Foreign key from module table
            $table->foreign('module_id')->references('id')->on('modules')->onDelete('cascade');

            // Column for user id who created the module permission
            $table->integer('created_by')->default(0);

            // Column for user id who modified the module permission
            $table->integer('modified_by')->default(0);

            // module permission created time and modified time columns
            $table->timestamps();

            // for soft delete
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_module_permission');
    }
};
