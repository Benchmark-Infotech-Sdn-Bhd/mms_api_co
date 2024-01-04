<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;

class EContractPayroll extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable, SoftDeletes;

    protected $table = 'e-contract_payroll';
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'worker_id', 'project_id', 'month', 'year', 'basic_salary', 'ot_1_5', 'ot_2_0', 'ot_3_0', 'ph', 'rest_day', 'deduction_advance', 'deduction_accommodation', 'annual_leave', 'medical_leave', 'hospitalisation_leave', 'amount', 'no_of_workingdays', 'normalday_ot_1_5', 'ot_1_5_hrs_amount', 'restday_daily_salary_rate', 'hrs_ot_2_0', 'ot_2_0_hrs_amount', 'public_holiday_ot_3_0', 'deduction_hostel', 'created_by', 'modified_by', 'sosco_deduction', 'sosco_contribution'
    ];
}
