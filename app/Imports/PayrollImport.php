<?php

namespace App\Imports;

use App\Jobs\PayrollsImport;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PayrollImport implements ToModel, WithChunkReading, WithHeadingRow
{
    protected const CHUNK_ROW = 250;
    private mixed $parameters;
    private string $bulkUpload;

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
     * Process and persist payroll data from a single row.
     *
     * @param array $row The payroll data for a single row.
     * @return void
     */
    public function model(array $row)
    {
        try {
            Log::info('Payroll Row Data', [$row]);
            $payrollParameter = $this->createPayrollParameter($row);
            DB::table('payroll_bulk_upload')->where('id', $this->bulkUpload->id)->increment('total_records');
            dispatch(new PayrollsImport($payrollParameter, $this->bulkUpload));
        } catch (Exception $exception) {
            Log::error('Error - ' . $exception->getMessage());
        }
    }

    /**
     * Create a payroll parameter array from the given row.
     *
     * @param array $row The row of data to create the payroll parameter for.
     * @return array The created payroll parameter array.
     */
    private function createPayrollParameter(array $row): array
    {
        return [
            'project_id' => $this->parameters['project_id'],
            'name' => $row['name'] ?? '',
            'passport_number' => $row['passport_number'] ?? '',
            'department' => $row['department'] ?? '',
            'bank_account' => $row['bank_account'] ?? '',
            'month' => $row['month'] ?? 0,
            'year' => $row['year'] ?? 0,
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
    }

    /**
     * Get the chunk size for processing data.
     *
     * @return int The chunk size for processing data.
     */
    public function chunkSize(): int
    {
        return self::CHUNK_ROW;
    }
}
