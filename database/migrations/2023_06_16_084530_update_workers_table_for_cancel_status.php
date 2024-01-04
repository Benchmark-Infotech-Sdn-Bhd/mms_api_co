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
        Schema::table('workers', function (Blueprint $table) {
            // Column for calling visa cancel status
            $table->tinyInteger('cancel_status')->default(0)->after('status');
            // Column for calling visa cancellation remarks
            if (DB::getDriverName() === 'sqlite') {
                $table->text('remarks')->default('')->after('cancel_status');
            } else {
                $table->text('remarks')->after('cancel_status');
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
