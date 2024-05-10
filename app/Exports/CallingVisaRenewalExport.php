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

class CallingVisaRenewalExport implements FromQuery, WithHeadings
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
     * Returns a query builder instance with a specific set of conditions and selected columns.
     *
     * @return Builder
     */
    public function query()
    {
        if($this->notificationType == Config::get('services.COMPANY_NOTIFICATION_TYPE')[0]) {
            return Workers::query()
                        ->join('worker_visa', 'workers.id', '=', 'worker_visa.worker_id')
                        ->whereDate('worker_visa.calling_visa_valid_until', '<', Carbon::now()->addDays($this->days))
                        ->whereDate('worker_visa.calling_visa_valid_until', '>=', Carbon::now())
                        ->where('workers.company_id', $this->companyId)
                        ->select('workers.name as worker_name', 'workers.gender', 'workers.passport_number', 'worker_visa.calling_visa_reference_number', 'worker_visa.calling_visa_valid_until as calling_visa_expiry_date');
        } else if($this->notificationType == Config::get('services.COMPANY_NOTIFICATION_TYPE')[1]) {
            return Workers::query()
                        ->join('worker_visa', 'workers.id', '=', 'worker_visa.worker_id')
                        ->whereDate('worker_visa.calling_visa_valid_until', '>=', Carbon::now()->subDays($this->days))
                        ->whereDate('worker_visa.calling_visa_valid_until', '<', Carbon::now())
                        ->where('workers.company_id', $this->companyId)
                        ->select('workers.name as worker_name', 'workers.gender', 'workers.passport_number', 'worker_visa.calling_visa_reference_number', 'worker_visa.calling_visa_valid_until as calling_visa_expiry_date');
        }
    }

    /**
     * Returns an array of column headings for a specific set of data.
     *
     * @return array
     */
    public function headings(): array
    {
        return ['worker_name', 'gender', 'passport_number', 'calling_visa_reference_number', 'calling_visa_expiry_date'];
    }
}
