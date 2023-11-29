<?php

namespace App\Exports;

use App\Models\Workers;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class SpecialPassRenewalExport implements FromQuery, WithHeadings
{
    use Exportable;

    public function __construct(int $companyId)
    {
        $this->companyId = $companyId;
    }

    public function query()
    {
        return Workers::query()
        ->whereDate('special_pass_valid_until', '<', Carbon::now()->addMonths(1))
        ->where('company_id', $this->companyId)
        ->select('name as worker_name', 'gender', 'passport_number', 'special_pass_submission_date', 'special_pass_valid_until as special_pass_expiry_date');
    }
    public function headings(): array
    {
        return ['worker_name', 'gender', 'passport_number', 'special_pass_submission_date', 'special_pass_expiry_date'];
    }
}
