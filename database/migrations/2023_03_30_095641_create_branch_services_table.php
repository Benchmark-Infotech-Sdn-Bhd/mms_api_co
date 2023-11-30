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
        Schema::create('branch_services', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('branch_id')->unsigned()->nullable();
            $table->foreign('branch_id')->references('id')->on('branch')->onDelete('cascade');
            $table->integer('service_id')->nullable()->unsigned()->index();
            $table->string('service_name', 255);
            $table->tinyInteger('status')->default(0)->unsigned()->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            Schema::dropIfExists('branch_services');
        }
    }
};
