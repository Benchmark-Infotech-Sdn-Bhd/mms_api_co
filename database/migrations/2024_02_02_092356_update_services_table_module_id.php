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
        Schema::table('services', function (Blueprint $table) {
            if (DB::getDriverName() !== 'sqlite') {
                // Column for module id
                $table->bigInteger('module_id')->unsigned()->after('status');
                // Foreign key from module table
                $table->foreign('module_id')->references('id')->on('modules')->onDelete('cascade');
            }
            // Column for module id
            $table->bigInteger('module_id')->default(0)->unsigned()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
