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
        Schema::create('directrecruitment_applications', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('crm_prospect_id')->unsigned();
            $table->foreign('crm_prospect_id')->references('id')->on('crm_prospects')->onDelete('cascade');
            $table->bigInteger('service_id')->unsigned();
            $table->foreign('service_id')->references('id')->on('crm_prospect_services')->onDelete('cascade');
            $table->integer('quota_applied')->nullable();
            $table->string('person_incharge',255)->nullable();
            $table->float('cost_quoted')->default(0.0);
            $table->string('status')->default('Proposal')->index();
            $table->string('service_type')->default('Direct Recruitment');
            $table->string('remarks',255)->nullable();
            $table->integer('created_by')->default(0);
            $table->integer('modified_by')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('directrecruitment_applications');
    }
};

