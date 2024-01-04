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
        Schema::table('onboarding_dispatch', function (Blueprint $table) {
            if (DB::getDriverName() !== 'sqlite') {
            $table->dropForeign('onboarding_dispatch_onboarding_attestation_id_foreign');
            }
            $table->unsignedBigInteger('onboarding_attestation_id')->nullable()->change();
            $table->enum('dispatch_status',['Assigned','Completed'])->default('Assigned');
            $table->enum('job_type',['Collect', 'Deliver', 'Submission', 'Bring FOMEMA', 'Counselling'])->default('Submission');
            $table->string('passport', 255)->nullable();
            $table->string('document_name')->nullable();
            $table->float('payment_amount')->default(0.0);
            $table->string('worker_name', 255)->nullable();
            $table->text('acknowledgement_remarks')->nullable();
            $table->date('acknowledgement_date')->nullable();
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
