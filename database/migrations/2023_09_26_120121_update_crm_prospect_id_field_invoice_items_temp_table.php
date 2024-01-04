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
        Schema::table('invoice_items_temp', function (Blueprint $table) {            
            if (DB::getDriverName() === 'sqlite') {
                // CRM Prospect ID column
                $table->bigInteger('crm_prospect_id')->default(0)->unsigned()->after('id');
            } else {
                // CRM Prospect ID column
                $table->bigInteger('crm_prospect_id')->unsigned()->after('id');
            }
            $table->foreign('crm_prospect_id')->references('id')->on('crm_prospects')->onDelete('cascade');
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
