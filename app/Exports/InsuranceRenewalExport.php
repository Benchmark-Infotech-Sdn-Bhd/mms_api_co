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

class InsuranceRenewalExport implements FromQuery, WithHeadings
{
    use Exportable;

    private int $companyId;

    private string $notificationType;

    private int $days;

    /**
     * Constructs a new object of the class.
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
     * Get the query to retrieve worker insurance details.
     *
     * @return Builder
     */
    public function query()
    {
        if($this->notificationType == Config::get('services.COMPANY_NOTIFICATION_TYPE')[0]) {
            return Workers::query()
                        ->join('worker_insurance_details', 'workers.id', '=', 'worker_insurance_details.worker_id')
                        ->whereDate('worker_insurance_details.insurance_expiry_date', '<', Carbon::now()->addDays($this->days))
                        ->whereDate('worker_insurance_details.insurance_expiry_date', '>=', Carbon::now())
                        ->where('workers.company_id', $this->companyId)
                        ->select('workers.name as worker_name', 'workers.gender', 'workers.passport_number', 'worker_insurance_details.ig_policy_number', 'worker_insurance_details.hospitalization_policy_number', 'worker_insurance_details.insurance_expiry_date as expiry_date');
        } else if($this->notificationType == Config::get('services.COMPANY_NOTIFICATION_TYPE')[1]) {
            return Workers::query()
                        ->join('worker_insurance_details', 'workers.id', '=', 'worker_insurance_details.worker_id')
                        ->whereDate('worker_insurance_details.insurance_expiry_date', '>=', Carbon::now()->subDays($this->days))
                        ->whereDate('worker_insurance_details.insurance_expiry_date', '<', Carbon::now())
                        ->where('workers.company_id', $this->companyId)
                        ->select('workers.name as worker_name', 'workers.gender', 'workers.passport_number', 'worker_insurance_details.ig_policy_number', 'worker_insurance_details.hospitalization_policy_number', 'worker_insurance_details.insurance_expiry_date as expiry_date');
        }
    }

    /**
     * Get the headings for worker insurance details.
     *
     * @return array
     */
    public function headings(): array
    {
        return ['worker_name', 'gender', 'passport_number', 'ig_policy_number', 'hospitalization_policy_number', 'expiry_date'];
    }
}
