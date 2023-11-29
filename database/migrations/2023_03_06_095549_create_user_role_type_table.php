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
        Schema::create('user_role_type', function (Blueprint $table) {

            // User role type id column
            $table->id();

            // Column for user id
            $table->bigInteger('user_id')->unsigned();

            // Foreign key from user table
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // Column for user role id
            $table->bigInteger('role_id')->unsigned();

            // Foreign key from Role table
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');

            // User role type status column
            $table->tinyInteger('status')->default(1)->unsigned()->index();

            // Column for user id who created the User role type
            $table->integer('created_by')->default(0);

            // Column for user id who modified the User role type
            $table->integer('modified_by')->default(0);

            // User role type created time and modified time columns
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
        if (DB::getDriverName() !== 'sqlite') {
            Schema::dropIfExists('user_role_type');
        }
    }
};
