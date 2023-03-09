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
        Schema::create('permissions', function (Blueprint $table) {
            
            // Permission id column
            $table->id();

            // Permission name column
            $table->string('permission_name', 150);

            // Permission status column
            $table->tinyInteger('status')->default(1)->unsigned()->index();

            // Permission created time and modified time columns
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
        Schema::dropIfExists('permissions');
    }
};
