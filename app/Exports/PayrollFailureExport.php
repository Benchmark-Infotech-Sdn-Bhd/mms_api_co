<?php

namespace App\Exports;

use App\Models\PayrollUploadRecords;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\DB;

class PayrollFailureExport implements FromQuery, WithHeadings
{
    use Exportable;

    public function __construct(int $id)
    {
        $this->id = $id;
    }

    public function query()
    {
        return PayrollUploadRecords::query()->where('success_flag', 0)
        ->where('bulk_upload_id', $this->id)
        ->select(DB::raw('json_unquote(JSON_EXTRACT(parameter, "$.name")) as name, json_unquote(JSON_EXTRACT(parameter, "$.passport_number")) as passport_number, json_unquote(JSON_EXTRACT(parameter, "$.department")) as department, json_unquote(JSON_EXTRACT(parameter, "$.bank_account")) as bank_account, json_unquote(JSON_EXTRACT(parameter, "$.month")) as month, json_unquote(JSON_EXTRACT(parameter, "$.year")) as year, json_unquote(JSON_EXTRACT(parameter, "$.basic_salary")) as basic_salary, json_unquote(JSON_EXTRACT(parameter, "$.ot_1_5")) as ot_at_15, json_unquote(JSON_EXTRACT(parameter, "$.ot_2_0")) as ot_at_20, json_unquote(JSON_EXTRACT(parameter, "$.ot_3_0")) as ot_at_30, json_unquote(JSON_EXTRACT(parameter, "$.ph")) as ph, json_unquote(JSON_EXTRACT(parameter, "$.rest_day")) as rest_day, json_unquote(JSON_EXTRACT(parameter, "$.deduction_advance")) as deduction_advance, json_unquote(JSON_EXTRACT(parameter, "$.deduction_accommodation")) as deduction_accommodation, json_unquote(JSON_EXTRACT(parameter, "$.annual_leave")) as annual_leave, json_unquote(JSON_EXTRACT(parameter, "$.medical_leave")) as medical_leave, json_unquote(JSON_EXTRACT(parameter, "$.hospitalisation_leave")) as hospitalisation_leave, json_unquote(JSON_EXTRACT(parameter, "$.amount")) as amount, json_unquote(JSON_EXTRACT(parameter, "$.no_of_workingdays")) as no_of_working_days_month, json_unquote(JSON_EXTRACT(parameter, "$.normalday_ot_1_5")) as normal_day_ot_at_15, json_unquote(JSON_EXTRACT(parameter, "$.ot_1_5_hrs_amount")) as ot_15hrs_amount_rm, json_unquote(JSON_EXTRACT(parameter, "$.restday_daily_salary_rate")) as restday_daily_salary_rate, json_unquote(JSON_EXTRACT(parameter, "$.hrs_ot_2_0")) as hrs_ot_at_20, json_unquote(JSON_EXTRACT(parameter, "$.ot_2_0_hrs_amount")) as ot_20hrs_amount_rm, json_unquote(JSON_EXTRACT(parameter, "$.public_holiday_ot_3_0")) as public_holiday_ot_at_30, json_unquote(JSON_EXTRACT(parameter, "$.deduction_hostel")) as deduction_hostel, json_unquote(JSON_EXTRACT(parameter, "$.sosco_deduction")) as sosco_deduction, json_unquote(JSON_EXTRACT(parameter, "$.sosco_contribution")) as sosco_contribution'), 'comments');
    }
    public function headings(): array
    {
        return ['name', 'passport_number', 'department', 'bank_account', 'month', 'year','basic_salary', 'ot_at_15', 'ot_at_20', 'ot_at_30', 'ph', 'rest_day', 'deduction_advance',	'deduction_accommodation', 'annual_leave', 'medical_leave', 'hospitalisation_leave','amount', 'no_of_working_days_month', 'normal_day_ot_at_15', 'ot_15hrs_amount_rm',	'restday_daily_salary_rate', 'hrs_ot_at_20', 'ot_20hrs_amount_rm', 'public_holiday_ot_at_30', 'deduction_hostel', 'sosco_deduction', 'sosco_contribution', 'comments'];
    }
}
