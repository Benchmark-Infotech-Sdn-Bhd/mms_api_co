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
        Schema::table('crm_prospects', function (Blueprint $table) {
            if (DB::getDriverName() !== 'sqlite') {
                // To drop foreign key
                $table->dropForeign('crm_prospects_sector_type_foreign');
                
            // To drop Sector id column
            $table->dropColumn('sector_type');
            // To drop contractn type column
            $table->dropColumn('contract_type');
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
