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
        Schema::create('xero_settings', function (Blueprint $table) {
            // Expenses Id column
            $table->id();
            // Title column
            $table->string('title', 255);
            // Remarks column
            $table->text('remarks')->nullable();
            // url column
            $table->text('url')->nullable();
            // Client Id column
            $table->text('client_id')->nullable();
            // Client Secret column
            $table->text('client_secret')->nullable();
            // Tenant Id column
            $table->text('tenant_id')->nullable();
            // Access Token column
            $table->text('access_token')->nullable();
            // Refresh Token column
            $table->text('refresh_token')->nullable();
            // Column for user id who created the expenses
            $table->integer('created_by')->default(0);
            // Column for user id who modified the expenses
            $table->integer('modified_by')->default(0);
            // expenses created time and modified time columns
            $table->timestamps();
            // for softdelete
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            Schema::dropIfExists('xero_settings');
        }
    }
};
