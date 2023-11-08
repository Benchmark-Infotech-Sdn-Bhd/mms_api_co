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
        Schema::create('countries', function (Blueprint $table) {
            // Countries Id column
            $table->id();
            // Countries Name column
            $table->string('country_name',150);
            // Countries System type column
            $table->enum('system_type',['Embassy','FWCMS'])->default('FWCMS');
            // Countries Costing status column
            $table->enum('costing_status',['Pending','Done'])->default('Pending');
            // Countries Fee column
            $table->float('fee')->nullable();
            // Countries Bond column
            $table->integer('bond')->nullable();
            // Column for user Id, who created the countries
            $table->integer('created_by')->default(0);
            // Column for user Id, who modified the countries
            $table->integer('modified_by')->default(0);
            // Countries created_at and updated_at columns
            $table->timestamps();
            // softdelete for Countries
            $table->softDeletes();
            // Unique field for Countries
            $table->unique(['country_name', 'deleted_at']);
            // Indexing for Countries based on Id, System type, Country name, Costing status columns
            $table->index(['id','system_type','country_name','costing_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            Schema::dropIfExists('countries');
        }
    }
};
