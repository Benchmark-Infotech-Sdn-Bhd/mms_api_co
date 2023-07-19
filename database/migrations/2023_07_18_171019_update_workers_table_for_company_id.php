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
            // CRM prospect id column
            $table->bigInteger('crm_prospect_id')->unsigned()->nullable();
            // Drop foreign columns
            if (DB::getDriverName() !== 'sqlite') {
                $table->dropForeign('workers_onboarding_country_id_foreign');
                $table->dropForeign('workers_agent_id_foreign');
                $table->dropForeign('workers_application_id_foreign');
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
