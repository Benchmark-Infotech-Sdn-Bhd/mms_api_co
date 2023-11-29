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
        Schema::create('roles', function (Blueprint $table) {

            // Role id column
            $table->id();

            // Role name column
            $table->string('role_name', 150);

            // System role column
            $table->integer('system_role')->nullable();

            // Role status column
            $table->tinyInteger('status')->default(1)->unsigned()->index();

            // Role parent id column
            $table->integer('parent_id')->nullable()->unsigned()->index();

            // Column for user id who created the Role 
            $table->integer('created_by')->default(0);

            // Column for user id who modified the Role 
            $table->integer('modified_by')->default(0);

            // Role created time and modified time columns
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
            Schema::dropIfExists('roles');
        }
    }
};
