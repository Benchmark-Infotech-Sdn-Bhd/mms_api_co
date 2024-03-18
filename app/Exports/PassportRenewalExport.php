<?php

namespace App\Exports;

use App\Models\Workers;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class PassportRenewalExport implements FromQuery, WithHeadings
{
    use Exportable;

    private int $companyId;

    /**
     * Constructor for the class.
     *
     * @param int $companyId The ID of the company associated with the object.
     *
     * @return void
     */
    public function __construct(int $companyId)
    {
        $this->companyId = $companyId;
    }

    /**
     * Retrieve a query for fetching workers.
     *
     * The returned query will filter workers based on the following conditions:
     * - The "passport_valid_until" field should be less than the current date plus 3 months.
     * - The "company_id" field should match the value of the current object's "companyId" property.
     *
     * The selected columns in the query will include:
     * - "name" with an alias "worker_name".
     * - "gender".
     * - "passport_number".
     * - "passport_valid_until" with an alias "passport_expiry_date".
     *
     * @return Builder The query for fetching workers.
     */
    public function query()
    {
        return Workers::query()
            ->whereDate('passport_valid_until', '<', Carbon::now()->addMonths(3))
            ->where('company_id', $this->companyId)
            ->select('name as worker_name', 'gender', 'passport_number', 'passport_valid_until as passport_expiry_date');
    }

    /**
     * Retrieve the headings for the worker data.
     *
     * The returned array will contain the following headings in the specified order:
     * 1. "worker_name" - The name of the worker.
     * 2. "gender" - The gender of the worker.
     * 3. "passport_number" - The passport number of the worker.
     * 4. "passport_expiry_date" - The expiry date of the worker's passport.
     *
     * @return array The headings for the worker data.
     */
    public function headings(): array
    {
        return ['worker_name', 'gender', 'passport_number', 'passport_expiry_date'];
    }
}
