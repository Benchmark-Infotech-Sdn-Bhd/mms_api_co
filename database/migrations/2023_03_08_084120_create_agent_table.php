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
            // Agent Id column
            $table->id();
            // Agent name column
            $table->string('agent_name',250);
            // Agent country Id column
            $table->unsignedBigInteger('country_id');
            // Agent city column
            $table->string('city',150)->nullable();
            // Agent Person in charge column
            $table->string('person_in_charge',255);
            // Agent PIC contact number column
            $table->bigInteger('pic_contact_number')->default(0);
            // Agent email address column
            $table->string('email_address',150);
            // Agent company address column
            $table->text('company_address')->nullable();
            // Column for user Id, who created the agent
            $table->integer('created_by')->default(0);
            // Column for user Id, who modified the agent
            $table->integer('modified_by')->default(0);
            // Agents created_at and updated_at columns
            $table->timestamps();
            // softdelete for agent
            $table->softDeletes();
            // Unique field for agent
            $table->unique(['email_address', 'deleted_at']);
            // Foreign key from countries table
            $table->foreign('country_id')
              ->references('id')->on('countries')->onDelete('cascade');
            // Indexing for Agent based on Id,Name, Country Id, city, PIC columns
            $table->index(['id','agent_name','country_id','city','person_in_charge']);
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
