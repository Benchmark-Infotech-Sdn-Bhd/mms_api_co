<?php

namespace App\Exports;

use App\Models\Workers;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Carbon;

class SpecialPassRenewalExport implements FromQuery, WithHeadings
{
    use Exportable;

    private int $companyId;

    /**
     * Constructor method for the class.
     *
     * @param int $companyId The ID of the company.
     * @return void
     */
    public function __construct(int $companyId)
    {
        $this->companyId = $companyId;
    }

    /**
     * Returns a query builder instance for retrieving specific worker data.
     *
     * @return Builder
     */
    public function query()
    {
        return Workers::query()
            ->whereDate('special_pass_valid_until', '<', Carbon::now()->addMonths(1))
            ->where('company_id', $this->companyId)
            ->select('name as worker_name', 'gender', 'passport_number', 'special_pass_submission_date', 'special_pass_valid_until as special_pass_expiry_date');
    }

    /**
     * Returns an array of column headings for worker data.
     *
     * @return array
     */
    public function headings(): array
    {
        return ['worker_name', 'gender', 'passport_number', 'special_pass_submission_date', 'special_pass_expiry_date'];
    }
}
