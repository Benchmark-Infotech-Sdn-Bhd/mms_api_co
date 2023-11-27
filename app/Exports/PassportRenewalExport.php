<?php

namespace App\Exports;

use App\Models\Workers;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class PassportRenewalExport implements FromQuery, WithHeadings
{
    use Exportable;

    public function __construct(int $companyId)
    {
        $this->companyId = $companyId;
    }

    public function query()
    {
        return Workers::query()
        ->whereDate('passport_valid_until', '<', Carbon::now()->addMonths(3))
        ->where('company_id', $this->companyId)
        ->select('name as worker_name', 'gender', 'passport_number', 'passport_valid_until as passport_expiry_date');
    }
    public function headings(): array
    {
        return ['worker_name', 'gender', 'passport_number', 'passport_expiry_date'];
    }
}
