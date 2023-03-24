<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * @return void
     */
    public function up(): void
    {
        Schema::create('role_permission', function (Blueprint $table) {

            // User role permission id column
            $table->id();

            // Column for user id
            $table->bigInteger('role_id')->unsigned();

            // Foreign key from user table
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');

            // Column for module id
            $table->bigInteger('module_id')->unsigned();

            // Foreign key from module table
            $table->foreign('module_id')->references('id')->on('modules')->onDelete('cascade');

            // Permission id column
            $table->bigInteger('permission_id')->unsigned();

            // Column for user id who created the role permission
            $table->integer('created_by')->default(0);

            // Column for user id who modified the role permission
            $table->integer('modified_by')->default(0);

            // Role permission created time and modified time columns
            $table->timestamps();

            // for soft delete
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     * 
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('role_permission');
    }
};
