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
        Schema::table('workers', function (Blueprint $table) {
            // Column for Special Pass
            $table->tinyInteger('special_pass')->default(0)->after('replace_at');
            // Column for Special Pass submission date
            $table->date('special_pass_submission_date')->nullable()->after('special_pass');
            // Column for special pass validity
            $table->date('special_pass_valid_until')->nullable()->change();
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
