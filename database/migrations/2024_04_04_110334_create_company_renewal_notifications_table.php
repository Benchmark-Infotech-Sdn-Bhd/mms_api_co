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
        Schema::create('company_renewal_notifications', function (Blueprint $table) {
            // Column for company notification id
            $table->id();
            // Column for renewal notification id
            $table->bigInteger('notification_id')->unsigned();
            // Foreign key from renewal notifications table
            $table->foreign('notification_id')->references('id')->on('renewal_notifications')->onDelete('cascade');
            // Column for company_id from company table
            $table->bigInteger('company_id')->unsigned();
            // Foreign key from company table
            $table->foreign('company_id')->references('id')->on('company')->onDelete('cascade');
            // Column for Renewal Notification status
            $table->tinyInteger('renewal_notification_status')->default(0)->unsigned()->index();
            // Column for days, the renewal notification to be sent before item expiry
            $table->integer('renewal_duration_in_days')->default(0);
            // Column for renewal notification cycle
            $table->enum('renewal_frequency_cycle',['Daily', 'Weekly', 'Monthly'])->default('Daily')->index();
            // Column for Expired Notification status
            $table->tinyInteger('expired_notification_status')->default(0)->unsigned()->index();
            // Column for days, the expired notification to be sent after item expiry
            $table->integer('expired_duration_in_days')->default(0);
            // Column for expired notification cycle
            $table->enum('expired_frequency_cycle',['Daily', 'Weekly', 'Monthly'])->default('Weekly')->index();
            // Column for user id who created the company notification
            $table->integer('created_by')->default(0);
            // Column for user id who modified the company notification
            $table->integer('modified_by')->default(0);
            // Columns for company notification created and updated time
            $table->timestamps();
            // for soft delete
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_renewal_notifications');
    }
};
