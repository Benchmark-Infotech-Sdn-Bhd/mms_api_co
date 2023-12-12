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
        Schema::table('total_management_expenses', function (Blueprint $table) {
            if (DB::getDriverName() !== 'sqlite') {
                $table->dropForeign('total_management_expenses_application_id_foreign');
            }
            $table->dropColumn('application_id');
            if (DB::getDriverName() !== 'sqlite') {
                $table->string('payment_reference_number')->nullable()->change();
                $table->integer('quantity')->nullable()->change();
            }

            if (DB::getDriverName() === 'sqlite') {
                $table->bigInteger('worker_id')->default(0)->unsigned();
                $table->enum('type', ['Advance', 'Deposit', 'Payroll'])->default('')->index();
            } else {
                $table->bigInteger('worker_id')->unsigned();
                $table->enum('type', ['Advance', 'Deposit', 'Payroll'])->index();
            }
            $table->foreign('worker_id')->references('id')->on('workers')->onDelete('cascade');
            $table->decimal('deduction', 8, 2)->default(0);
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
