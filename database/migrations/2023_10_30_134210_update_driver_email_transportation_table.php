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
        Schema::table('transportation', function (Blueprint $table) {
            // Transportation Driver Email column
            $table->string('driver_email',150)->nullable()->after('driver_contact_number');
            // Assigned_supervisor column
            $table->integer('assigned_supervisor')->default(0)->after('vendor_id');;
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
