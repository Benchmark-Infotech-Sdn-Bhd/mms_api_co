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
        Schema::create('user_company', function (Blueprint $table) {
            // Cloumn for id
            $table->id();
            // Column for user_id from  user table
            $table->bigInteger('user_id')->unsigned();
            // Foreign key from user table
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            // Column for company_id from company table
            $table->bigInteger('company_id')->unsigned();
            // Foreign key from company table
            $table->foreign('company_id')->references('id')->on('company')->onDelete('cascade');
            // Column for role_id from roles table
            $table->bigInteger('role_id')->unsigned();
            // Foreign key from roles table
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
            // Column for user id who created the user 
            $table->integer('created_by')->default(0);
            // Column for user id who modified the user
            $table->integer('modified_by')->default(0);
            // Column user created and updated time
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_company');
    }
};
