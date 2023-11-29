<?php

namespace App\Exports;

use App\Models\Workers;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class CallingVisaRenewalExport implements FromQuery, WithHeadings
{
    use Exportable;

    public function __construct(int $companyId)
    {
        $this->companyId = $companyId;
    }

    public function query()
    {
        return Workers::query()
        ->join('worker_visa', 'workers.id', '=', 'worker_visa.worker_id')
        ->whereDate('worker_visa.calling_visa_valid_until', '<', Carbon::now()->addMonths(1))
        ->where('workers.company_id', $this->companyId)
        ->select('workers.name as worker_name', 'workers.gender', 'workers.passport_number', 'worker_visa.calling_visa_reference_number', 'worker_visa.calling_visa_valid_until as calling_visa_expiry_date');
    }
    public function headings(): array
    {
        return ['worker_name', 'gender', 'passport_number', 'calling_visa_reference_number', 'calling_visa_expiry_date'];
    }
}
