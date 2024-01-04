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
        Schema::table('crm_prospect_services', function (Blueprint $table) {
            // Column for from existing flag
            $table->tinyInteger('from_existing')->default(0)->after('status');
            // Column for client quota
            $table->integer('client_quota')->default(0)->after('from_existing');
            // Column for fomnext quota
            $table->integer('fomnext_quota')->default(0)->after('client_quota');
            // Column for initial quota
            $table->integer('initial_quota')->default(0)->after('fomnext_quota');
            // Column for service quota
            $table->integer('service_quota')->default(0)->after('initial_quota');
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
