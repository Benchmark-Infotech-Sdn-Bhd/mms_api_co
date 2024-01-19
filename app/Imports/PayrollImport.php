<?php

namespace App\Imports;

use App\Jobs\PayrollsImport;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Config;

class PayrollImport implements ToModel, WithChunkReading, WithHeadingRow
{
    protected const CHUNK_ROW = 250;
    private $parameters;
    /**
     * @var string
     */
    private $bulkUpload;

    /**
     * Create a new job instance.
     *
     * @param $parameters
     * @param string $bulkUpload
     */
    public function __construct($parameters, $bulkUpload = '')
    {
        $this->parameters = $parameters;
        $this->bulkUpload = $bulkUpload;
    }

    /**
     * @param array $row
     * @return Model|Model[]|void|null
     */
    public function model(array $row)
    {
        try {
                //Log::info('Payroll Row Data' . print_r($row, true));

                $payrollParameter = [
                    'project_id' => $this->parameters['project_id'],
                    'company_id' => $this->parameters['company_id'],
                    'name' => $row['name'] ?? '',
                    'passport_number' => $row['passport_number'] ?? '',
                    'department' => $row['department'] ?? '',
                    'bank_account' => $row['bank_account'] ?? '',
                    'month' => $row['month'] ?? '',
                    'year' => $row['year'] ?? '',
                    'basic_salary' => $row['basic_salary'] ?? 0,
                    'ot_1_5' => $row['ot_at_15'] ?? 0,
                    'ot_2_0' => $row['ot_at_20'] ?? 0,
                    'ot_3_0' => $row['ot_at_30'] ?? 0,
                    'ph' => $row['ph'] ?? 0,
                    'rest_day' => $row['rest_day'] ?? 0,
                    'deduction_advance' => $row['deduction_advance'] ?? 0,
                    'deduction_accommodation' => $row['deduction_accommodation'] ?? 0,
                    'annual_leave' => $row['annual_leave'] ?? 0,
                    'medical_leave' => $row['medical_leave'] ?? 0,
                    'hospitalisation_leave' => $row['hospitalisation_leave'] ?? 0,
                    'amount' => $row['amount'] ?? 0,
                    'no_of_workingdays' => $row['no_of_working_days_month'] ?? 0,
                    'normalday_ot_1_5' => $row['normal_day_ot_at_15'] ?? 0,
                    'ot_1_5_hrs_amount' => $row['ot_15hrs_amount_rm'] ?? 0,
                    'restday_daily_salary_rate' => $row['rest_day_daily_salary_rate'] ?? 0,
                    'hrs_ot_2_0' => $row['hrs_ot_at_20'] ?? 0,
                    'ot_2_0_hrs_amount' => $row['ot_20hrs_amount_rm'] ?? 0,
                    'public_holiday_ot_3_0' => $row['public_holiday_ot_at_30'] ?? 0,
                    'deduction_hostel' => $row['deduction_hostel'] ?? 0,
                    'sosco_deduction' => $row['sosco_deduction'] ?? 0,
                    'sosco_contribution' => $row['sosco_contribution'] ?? 0,
                    'created_by' => $this->parameters['created_by'] ?? 0,
                    'modified_by' => $this->parameters['created_by'] ?? 0
                ];
                
                DB::table('payroll_bulk_upload')->where('id', $this->bulkUpload->id)->increment('total_records');
                dispatch(new PayrollsImport(Config::get('database.connections.mysql.database'), $payrollParameter, $this->bulkUpload))->onQueue(Config::get('services.PAYROLL_IMPORT'))->onConnection(Config::get('services.QUEUE_CONNECTION'));

        } catch (\Exception $exception) {
            Log::error('Error - ' . print_r($exception->getMessage(), true));
        }
    }

    /**
     * @param $date
     * @return string
     */
    public function dateConvert($date)
    {
        return date('Y-m-d',strtotime($date));
    }

    /**
     * @return int
     */
    public function chunkSize(): int
    {
        return self::CHUNK_ROW;
    }
}
