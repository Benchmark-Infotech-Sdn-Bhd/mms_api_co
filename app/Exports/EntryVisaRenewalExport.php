<?php

namespace App\Exports;

use App\Models\Workers;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class EntryVisaRenewalExport implements FromQuery, WithHeadings
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
     * Executes a query to retrieve worker information along with their visa details.
     * The query joins the "worker_visa" table with the "workers" table using the worker_id as the foreign key.
     * It then applies a where condition to filter out workers whose entry visa is expiring within the next 15 days.
     * Finally, it selects specific columns from both tables and returns the query builder instance.
     *
     * @return Builder The query builder instance with applied joins, conditions, and column selection.
     */
    public function query()
    {
        return Workers::query()
            ->join('worker_visa', 'workers.id', '=', 'worker_visa.worker_id')
            ->whereDate('worker_visa.entry_visa_valid_until', '<', Carbon::now()->addDays(15))
            ->where('workers.company_id', $this->companyId)
            ->select('workers.name as worker_name', 'workers.gender', 'workers.passport_number', 'worker_visa.entry_visa_valid_until as entry_visa_expiry_date');
    }

    /**
     * Get the headings for the worker data.
     *
     * @return array The array of headings. The headings will include 'worker_name', 'gender', 'passport_number', and 'entry_visa_expiry_date'.
     */
    public function headings(): array
    {
        return ['worker_name', 'gender', 'passport_number', 'entry_visa_expiry_date'];
    }
}
