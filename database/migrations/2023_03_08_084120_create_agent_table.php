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
        Schema::create('agent', function (Blueprint $table) {
            $table->id();
            $table->string('agent_name',250);
            $table->string('country',150);
            $table->string('city',150)->nullable();
            $table->string('person_in_charge',255);
            $table->string('pic_contact_number',20);
            $table->string('email_address',150);
            $table->text('company_address');
            $table->integer('created_by')->default(0);
            $table->integer('modified_by')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['country']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agent');
    }
};
