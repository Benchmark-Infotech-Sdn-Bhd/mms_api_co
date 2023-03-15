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
        Schema::create('vendor_attachments', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('file_id')->unsigned()->nullable();
            $table->foreign('file_id')->references('id')->on('vendors')->onDelete('cascade');
            $table->string('file_name', 255);
            $table->string('file_type', 255);
            $table->text('file_url')->nullable();
            $table->integer('created_by')->default(0);
            $table->integer('modified_by')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_attachments');
    }
};
