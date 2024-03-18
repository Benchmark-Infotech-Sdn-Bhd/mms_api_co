<?php

namespace App\Exports;

use App\Models\Workers;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class PlksRenewalExport implements FromQuery, WithHeadings
{
    use Exportable;

    private int $companyId;

    /**
     * Class constructor.
     *
     * @param int $companyId The ID of the company.
     *
     * @return void
     */
    public function __construct(int $companyId)
    {
        $this->companyId = $companyId;
    }

    /**
     * Returns a query instance for retrieving workers with specific conditions.
     *
     * The query filters workers based on the following conditions:
     * - The 'plks_expiry_date' is before the current date plus 2 months.
     * - The 'company_id' matches the value stored in the property '$companyId'.
     *
     * The query selects the following fields:
     * - 'name' as 'worker_name'
     * - 'gender'
     * - 'passport_number'
     * - 'plks_expiry_date'
     *
     * @return Builder The query builder instance for retrieving workers.
     */
    public function query()
    {
        return Workers::query()
            ->whereDate('plks_expiry_date', '<', Carbon::now()->addMonths(2))
            ->where('company_id', $this->companyId)
            ->select('name as worker_name', 'gender', 'passport_number', 'plks_expiry_date');
    }

    /**
     * Returns an array of headings for worker data.
     *
     * The headings represent the following fields:
     * - 'worker_name': The name of the worker.
     * - 'gender': The gender of the worker.
     * - 'passport_number': The passport number of the worker.
     * - 'plks_expiry_date': The expiry date of the PLKS (Permit Letter of the Knowledgeable Worker Scheme) for the worker.
     *
     * @return array An array of headings for worker data.
     */
    public function headings(): array
    {
        return ['worker_name', 'gender', 'passport_number', 'plks_expiry_date'];
    }
}
