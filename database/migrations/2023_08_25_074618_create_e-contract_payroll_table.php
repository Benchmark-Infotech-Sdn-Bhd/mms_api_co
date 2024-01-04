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
        Schema::create('e-contract_payroll', function (Blueprint $table) {
            // column for id
            $table->id();
            // column worker id
            $table->bigInteger('worker_id')->unsigned()->nullable();
            // Foreign key from worker table
            $table->foreign('worker_id')->references('id')->on('workers')->onDelete('cascade');
            // column for project id
            $table->bigInteger('project_id')->unsigned()->nullable();
            // Foreign key from project table
            $table->foreign('project_id')->references('id')->on('e-contract_project')->onDelete('cascade');
            // Column for month 
            $table->integer('month')->default(0);
            // Column for year
            $table->integer('year')->default(0);
            // Column for basic salary
            $table->float('basic_salary')->default(0.0);
            // Column for ot_1_5
            $table->float('ot_1_5')->default(0.0);
            // Column for ot_2_0
            $table->float('ot_2_0')->default(0.0);
            // Column for ot_3_0
            $table->float('ot_3_0')->default(0.0);
            // Column for ph 
            $table->float('ph')->default(0.0);
            // Column for rest_day 
            $table->float('rest_day')->default(0.0);
            //deduction_advance
            $table->float('deduction_advance')->default(0.0);
            //deduction_accommodation
            $table->float('deduction_accommodation')->default(0.0);
            // column for annual leave
            $table->integer('annual_leave')->default(0);
            // column for medical leave
            $table->integer('medical_leave')->default(0);
            // column for hospitalisation leave
            $table->integer('hospitalisation_leave')->default(0);
            //Column for amount
            $table->float('amount')->default(0.0);
            //Column for no of workingdays
            $table->integer('no_of_workingdays')->default(0);
            //Column for normalday ot_1_5
            $table->float('normalday_ot_1_5')->default(0.0);
            //Column for ot_1_5_hrs_amount
            $table->float('ot_1_5_hrs_amount')->default(0.0);
            //Column for restday_daily_salary_rate
            $table->float('restday_daily_salary_rate')->default(0.0);
            //Column for hrs_ot_2_0
            $table->float('hrs_ot_2_0')->default(0.0);
            //Column for  ot_2_0_hrs_amount
            $table->float('ot_2_0_hrs_amount')->default(0.0);
            //Column for public_holiday_ot_3_0
            $table->float('public_holiday_ot_3_0')->default(0.0);
            //Column for deduction hostel
            $table->float('deduction_hostel')->default(0.0);
            // Column for sosco_deduction
            $table->float('sosco_deduction')->default(0.0);
            // Column for sosco_contribution
            $table->float('sosco_contribution')->default(0.0);
            // Column for user id who created the payroll 
            $table->integer('created_by')->default(0);
            // Column for user id who modified the payroll 
            $table->integer('modified_by')->default(0);     
            // vendor created time and modified time columns        
            $table->timestamps();
            // for soft delete
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            Schema::dropIfExists('e-contract_payroll');
        }
    }
};
