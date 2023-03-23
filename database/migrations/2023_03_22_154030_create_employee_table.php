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
        Schema::create('employee', function (Blueprint $table) {
            $table->id();
            $table->string('employee_name',255);
            $table->string('gender',15);
            $table->date('date_of_birth');
            $table->integer('ic_number');
            $table->string('passport_number')->nullable();
            $table->string('email',150);
            $table->bigInteger('contact_number');
            $table->text('address');
            $table->mediumInteger('postcode');
            $table->string('position',150);
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('role_id');
            $table->float('salary');
            $table->integer('created_by')->default(0);
            $table->integer('modified_by')->default(0);
            $table->timestamps();
            $table->foreign('branch_id')
              ->references('id')->on('branch')->onDelete('cascade');
            $table->foreign('role_id')
              ->references('id')->on('roles')->onDelete('cascade');
            $table->index(['branch_id','role_id']);
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee');
    }
};
