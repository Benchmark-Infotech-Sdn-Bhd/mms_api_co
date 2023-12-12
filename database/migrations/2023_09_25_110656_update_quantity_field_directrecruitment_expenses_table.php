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
        Schema::table('directrecruitment_expenses', function (Blueprint $table) {
            
            if (DB::getDriverName() === 'sqlite') {
                // Quantity column
                $table->integer('quantity')->default(0)->after('payment_date');
            } else {
                // Quantity column
                $table->integer('quantity')->after('payment_date');
            }
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
