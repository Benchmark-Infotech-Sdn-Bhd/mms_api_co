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
        Schema::table('worker_employment', function (Blueprint $table) {
            $table->string('work_start_date')->change();
            $table->string('work_end_date')->change();
            // transfer_flag 1 means, worker transferred to another project
            $table->tinyInteger('transfer_flag')->default(0)->after('service_type');
           
            if (DB::getDriverName() !== 'sqlite') {
                $table->dropColumn('transfer_start_date');
                $table->dropColumn('transfer_end_date');
            }
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
