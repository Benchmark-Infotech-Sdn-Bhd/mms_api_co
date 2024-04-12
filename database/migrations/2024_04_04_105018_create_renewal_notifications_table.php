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
        Schema::create('renewal_notifications', function (Blueprint $table) {
            // Renewal Notification id column
            $table->id();
            // Renewal Notification name column
            $table->string('notification_name', 150);
            // Renewal Notification status column
            $table->tinyInteger('status')->default(1)->unsigned()->index();
            // Renewal Notification created time and modified time columns
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
        Schema::dropIfExists('renewal_notifications');
    }
};
