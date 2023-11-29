<?php

namespace App\Exports;

use App\Models\Workers;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class PlksRenewalExport implements FromQuery, WithHeadings
{
    use Exportable;

    public function __construct(int $companyId)
    {
        $this->companyId = $companyId;
    }

    public function query()
    {
        return Workers::query()
        ->whereDate('plks_expiry_date', '<', Carbon::now()->addMonths(2))
        ->where('company_id', $this->companyId)
        ->select('name as worker_name', 'gender', 'passport_number', 'plks_expiry_date');
    }
    public function headings(): array
    {
        return ['worker_name', 'gender', 'passport_number', 'plks_expiry_date'];
    }
}
