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
        Schema::table('worker_bulk_upload', function (Blueprint $table) {
            if (DB::getDriverName() !== 'sqlite') {
                $table->dropForeign('worker_bulk_upload_onboarding_country_id_foreign');
                $table->dropForeign('worker_bulk_upload_agent_id_foreign');
                $table->dropForeign('worker_bulk_upload_application_id_foreign');
            }
            // Column for module type
            $table->string('module_type', 255)->nullable();
            $table->unsignedBigInteger('onboarding_country_id')->nullable()->after('id')->change();
            $table->unsignedBigInteger('agent_id')->nullable()->after('onboarding_country_id')->change();
            $table->unsignedBigInteger('application_id')->nullable()->after('agent_id')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('worker_bulk_upload', function (Blueprint $table) {
            //
        });
    }
};
