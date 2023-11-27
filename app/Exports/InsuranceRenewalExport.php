<?php

namespace App\Exports;

use App\Models\Workers;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class InsuranceRenewalExport implements FromQuery, WithHeadings
{
    use Exportable;

    public function __construct(int $companyId)
    {
        $this->companyId = $companyId;
    }

    public function query()
    {
        return Workers::query()
        ->join('worker_insurance_details', 'workers.id', '=', 'worker_insurance_details.worker_id')->whereDate('worker_insurance_details.insurance_expiry_date', '<', Carbon::now()->addMonths(1))
        ->where('workers.company_id', $this->companyId)
        ->select('workers.name as worker_name', 'workers.gender', 'workers.passport_number', 'worker_insurance_details.ig_policy_number', 'worker_insurance_details.hospitalization_policy_number', 'worker_insurance_details.insurance_expiry_date as expiry_date');
    }
    public function headings(): array
    {
        return ['worker_name', 'gender', 'passport_number', 'ig_policy_number', 'hospitalization_policy_number', 'expiry_date'];
    }
}
