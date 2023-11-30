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
        Schema::create('modules', function (Blueprint $table) {

            // Module id column
            $table->id();

            // Module name column
            $table->string('module_name', 150);

            // Module url column
            $table->string('module_url')->nullable();

            // Module parent id column
            $table->integer('parent_id')->nullable()->unsigned()->index();

            // Module order id column
            $table->integer('order_id')->nullable();

            // Module status column
            $table->tinyInteger('status')->default(1)->unsigned()->index();

            // Column for user id who created the Module 
            $table->integer('created_by')->default(0);

            // Column for user id who modified the Module 
            $table->integer('modified_by')->default(0);

            // Module created time and modified time columns
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
            Schema::dropIfExists('modules');
        }
    }
};
