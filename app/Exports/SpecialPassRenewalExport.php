<?php

namespace App\Exports;

use App\Models\Workers;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;

class SpecialPassRenewalExport implements FromQuery, WithHeadings
{
    use Exportable;

    private int $companyId;

    private string $notificationType;

    private int $days;

    /**
     * Constructor method for the class.
     *
     * @param int $companyId The ID of the company.
     * @return void
     */
    public function __construct(int $companyId, string $notificationType, int $days)
    {
        $this->companyId = $companyId;
        $this->notificationType = $notificationType;
        $this->days = $days;
    }

    /**
     * Returns a query builder instance for retrieving specific worker data.
     *
     * @return Builder
     */
    public function query()
    {
            if($this->notificationType == Config::get('services.COMPANY_NOTIFICATION_TYPE')[0]) {
                return Workers::query()
                            ->whereDate('special_pass_valid_until', '<', Carbon::now()->addDays($this->days))
                            ->whereDate('special_pass_valid_until', '>=', Carbon::now())
                            ->where('company_id', $this->companyId)
                            ->select('name as worker_name', 'gender', 'passport_number', 'special_pass_submission_date', 'special_pass_valid_until as special_pass_expiry_date');
            } else if($this->notificationType == Config::get('services.COMPANY_NOTIFICATION_TYPE')[1]) {
                return Workers::query()
                            ->whereDate('special_pass_valid_until', '>=', Carbon::now()->subDays($this->days))
                            ->whereDate('special_pass_valid_until', '<', Carbon::now())
                            ->where('company_id', $this->companyId)
                            ->select('name as worker_name', 'gender', 'passport_number', 'special_pass_submission_date', 'special_pass_valid_until as special_pass_expiry_date');
            }
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
