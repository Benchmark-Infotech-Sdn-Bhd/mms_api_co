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
        Schema::table('total_management_project', function (Blueprint $table) {            
            if (DB::getDriverName() === 'sqlite') {
                // Column for supervisor id
                $table->bigInteger('supervisor_id')->default(0)->unsigned()->after('address');
                // Column for supervisor type
                $table->enum('supervisor_type', ['employee', 'driver'])->default('')->after('supervisor_id');
            } else {
                // Column for supervisor id
                $table->bigInteger('supervisor_id')->unsigned()->after('address');
                // Column for supervisor type
                $table->enum('supervisor_type', ['employee', 'driver'])->after('supervisor_id');
            }
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('total_management_project', function (Blueprint $table) {
            //
        });
    }
};
