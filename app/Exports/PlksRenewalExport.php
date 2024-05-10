<?php

namespace App\Exports;

use App\Models\Workers;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;

class PlksRenewalExport implements FromQuery, WithHeadings
{
    use Exportable;

    private int $companyId;

    private string $notificationType;

    private int $days;

    /**
     * Class constructor.
     *
     * @param int $companyId The ID of the company.
     *
     * @return void
     */
    public function __construct(int $companyId, string $notificationType, int $days)
    {
        $this->companyId = $companyId;
        $this->notificationType = $notificationType;
        $this->days = $days;
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
        if($this->notificationType == Config::get('services.COMPANY_NOTIFICATION_TYPE')[0]) {
            return Workers::query()
                    ->join('worker_visa', 'workers.id', '=', 'worker_visa.worker_id')
                    ->where(function($query) {
                        $query->whereDate('workers.plks_expiry_date', '<', Carbon::now()->addDays($this->days))
                        ->orWhereDate('worker_visa.work_permit_valid_until', '<',  Carbon::now()->addDays($this->days));
                    })
                    ->where(function ($query) {
                        $query->whereDate('workers.plks_expiry_date', '>=', Carbon::now())
                        ->orWhereDate('worker_visa.work_permit_valid_until', '>=', Carbon::now());
                    })
                    ->where('workers.company_id', $this->companyId)
                    ->select('workers.name as worker_name', 'workers.gender', 'workers.passport_number', 'workers.plks_expiry_date');
        } else if($this->notificationType == Config::get('services.COMPANY_NOTIFICATION_TYPE')[1]) {
            return Workers::query()
                    ->join('worker_visa', 'workers.id', '=', 'worker_visa.worker_id')
                    ->where(function($q) {
                        $q->where(function($query) {
                            $query->whereDate('workers.plks_expiry_date', '>=', Carbon::now()->subDays($this->days))
                            ->whereDate('workers.plks_expiry_date', '<', Carbon::now());
                        })
                        ->orWhere(function($query) {
                            $query->whereDate('worker_visa.work_permit_valid_until', '>=', Carbon::now()->subDays($this->days))
                            ->whereDate('worker_visa.work_permit_valid_until', '<', Carbon::now());
                        });
                    })
                    ->where('workers.company_id', $this->companyId)
                    ->select('workers.name as worker_name', 'workers.gender', 'workers.passport_number', 'workers.plks_expiry_date');
        }
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
