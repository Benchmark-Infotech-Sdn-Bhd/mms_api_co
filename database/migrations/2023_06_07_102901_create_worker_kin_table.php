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
        Schema::create('worker_kin', function (Blueprint $table) {
            // Worker kin id column
            $table->id();
            // worker kin workerid column
            $table->bigInteger('worker_id')->unsigned()->nullable();
            // Foreign key from Worker table
            $table->foreign('worker_id')->references('id')->on('workers')->onDelete('cascade');
            // Column for Worker kin name
            $table->string('kin_name', 255);
            // Column for Worker kin relationship name
            $table->integer('kin_relationship_id');
            // Column for Worker kin contact number column
            $table->bigInteger('kin_contact_number')->default(0);
            // Column for user id who created the Worker
            $table->integer('created_by')->default(0);
            // Column for user id who modified the Worker
            $table->integer('modified_by')->default(0);
            // Worker created time and modified time columns 
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
        Schema::dropIfExists('worker_kin');
    }
};
