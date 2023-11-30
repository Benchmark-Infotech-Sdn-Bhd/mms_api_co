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
        Schema::create('users', function (Blueprint $table) {
            // User id column
            $table->id();
            // User name column
            $table->string('name', 150);
            // User email column
            $table->string('email', 250);
            // User login password column
            $table->string('password')->nullable();
            // Column for user id who created the User
            $table->integer('created_by')->default(0);
            // Column for user id who modified the User
            $table->integer('modified_by')->default(0);
            // User created time and modified time columns
            $table->timestamps();
            // for soft delete
            $table->softDeletes();
            // Unique field for users
            $table->unique(['email', 'deleted_at']);
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
            Schema::dropIfExists('users');
        }
    }
};
