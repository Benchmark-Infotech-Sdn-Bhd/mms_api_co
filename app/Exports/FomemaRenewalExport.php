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

class FomemaRenewalExport implements FromQuery, WithHeadings
{
    use Exportable;

    private int $companyId;

    private string $notificationType;

    private int $days;

    /**
     * __construct
     *
     * Initializes a new instance of the class with the provided companyId.
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
     * Retrieves a query builder instance for retrieving worker data based on certain conditions.
     *
     * @return Builder The query builder instance.
     */
    public function query()
    {
        if($this->notificationType == Config::get('services.COMPANY_NOTIFICATION_TYPE')[0]) {
            return Workers::query()
                    ->whereDate('fomema_valid_until', '<', Carbon::now()->addDays($this->days))
                    ->whereDate('fomema_valid_until', '>=', Carbon::now())
                    ->where('company_id', $this->companyId)
                    ->select('name as worker_name', 'gender', 'passport_number', 'fomema_valid_until as fomema_expiry_date');
        } else if($this->notificationType == Config::get('services.COMPANY_NOTIFICATION_TYPE')[1]) {
            return Workers::query()
                    ->whereDate('fomema_valid_until', '>=', Carbon::now()->subDays($this->days))
                    ->whereDate('fomema_valid_until', '<', Carbon::now())
                    ->where('company_id', $this->companyId)
                    ->select('name as worker_name', 'gender', 'passport_number', 'fomema_valid_until as fomema_expiry_date');
        }
    }

    /**
     * Retrieves an array of headings for the worker data.
     * The headings include worker name, gender, passport number, and Fomema expiry date.
     *
     * @return array An array of headings.
     */
    public function headings(): array
    {
        return ['worker_name', 'gender', 'passport_number', 'fomema_expiry_date'];
    }
}
