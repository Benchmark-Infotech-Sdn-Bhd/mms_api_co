<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Config;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Workers;
use App\Models\TotalManagementPayroll;
use App\Models\TotalManagementPayrollAttachments;
use App\Models\PayrollBulkUpload;
use App\Models\TotalManagementProject;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\PayrollImport;
use App\Models\TotalManagementCostManagement;
use App\Exports\PayrollFailureExport;
use App\Services\AuthServices;

class TotalManagementPayrollServices
{
    public const ERROR_UNAUTHORIZED = ['unauthorizedError' => true];
    public const ERROR_EXISTS = ['existsError' => true];
    public const ERROR_NO_RECORDS = ['noRecords' => true];
    public const ERROR_INVALID_USER = ['InvalidUser' => true];
    public const ERROR_QUEUE = ['queueError' => true];

    public const PAYROLL_BULK_UPLOAD_TYPE = 'Payroll Bulk Upload';
    public const DEFAULT_TRANSFER_FLAG = 0;
    public const FILE_TYPE = 'Timesheet';
    public const DEFAULT_VALUE = 0;
    public const DEFAULT_ACTIVE_VALUE = 1;
    public const COSTMANAGEMENT_TYPE = 'Payroll';

    /**
     * @var workers
     */
    private Workers $workers;
    /**
     * @var totalManagementPayroll
     */
    private TotalManagementPayroll $totalManagementPayroll;
    /**
     * @var totalManagementPayrollAttachments
     */
    private TotalManagementPayrollAttachments $totalManagementPayrollAttachments;
    /**
     * @var payrollBulkUpload
     */
    private PayrollBulkUpload $payrollBulkUpload;
    /**
     * @var totalManagementCostManagement
     */
    private TotalManagementCostManagement $totalManagementCostManagement;
    /**
     * @var storage
     */
    private Storage $storage;
    /**
     * @var totalManagementProject
     */
    private TotalManagementProject $totalManagementProject;

    /**
     * @var AuthServices
     */
    private AuthServices $authServices;
    /**
     * TotalManagementPayrollServices constructor.
     *
     * @param TotalManagementProject $totalManagementProject  The totalManagementProject object.
     * @param TotalManagementPayroll $totalManagementPayroll  The totalManagementPayroll object.
     * @param TotalManagementPayrollAttachments $totalManagementPayrollAttachments  The totalManagementPayrollAttachments object.
     * @param PayrollBulkUpload $payrollBulkUpload  The payrollBulkUpload object.
     * @param TotalManagementCostManagement $totalManagementCostManagement  The totalManagementCostManagement object.
     * @param Storage $storage;  The storage object.
     * @param AuthServices $authServices; The authServices object
     */

    public function __construct(
        Workers                             $workers,
        TotalManagementPayroll              $totalManagementPayroll,
        TotalManagementPayrollAttachments   $totalManagementPayrollAttachments,
        Storage                             $storage,
        PayrollBulkUpload                   $payrollBulkUpload,
        TotalManagementProject              $totalManagementProject,
        TotalManagementCostManagement       $totalManagementCostManagement,
        AuthServices                        $authServices
    )
    {
        $this->workers = $workers;
        $this->totalManagementPayroll = $totalManagementPayroll;
        $this->totalManagementPayrollAttachments = $totalManagementPayrollAttachments;
        $this->storage = $storage;
        $this->payrollBulkUpload = $payrollBulkUpload;
        $this->totalManagementProject = $totalManagementProject;
        $this->totalManagementCostManagement = $totalManagementCostManagement;
        $this->authServices = $authServices;
    }
    /**
     * validate the add request data
     *
     * @return array  The validation error messages if validation fails, otherwise false.
     */
    public function addValidation(): array
    {
        return [
            'worker_id' => 'required',
            'project_id' => 'required',
            'month' => 'required',
            'year' => 'required'
        ];
    }
    /**
     * validate the update request data
     *
     * @return array  The validation error messages if validation fails, otherwise false.
     */
    public function updateValidation(): array
    {
        return [
            'id' => 'required'
        ];
    }
    /**
     * validate the upload timesheet request data
     *
     * @return array  The validation error messages if validation fails, otherwise false.
     */
    public function uploadTimesheetValidation(): array
    {
        return [
            'project_id' => 'required',
            'month' => 'required',
            'year' => 'required'
        ];
    }
    /**
     * validate the import request data
     *
     * @return array  The validation error messages if validation fails, otherwise false.
     */
    public function importValidation(): array
    {
        return [
            'project_id' => 'required'
        ];
    }

    /**
     * Enriches the given request data with user details.
     *
     * @param array $request The request data to be enriched.
     * @return mixed Returns the enriched request data.
     */
    private function enrichRequestWithUserDetails($request): mixed
    {
        $user = JWTAuth::parseToken()->authenticate();
        $request['created_by'] = $user['id'];
        $request['modified_by'] = $user['id'];
        $request['company_id'] = $this->authServices->getCompanyIds($user);

        return $request;
    }

    /**
     * Get the Authenticated User data
     *
     * @return mixed Returns the enriched request data.
     *
     */
    private function getAuthenticatedUser(): mixed
    {
        return JWTAuth::parseToken()->authenticate();
    }

    /**
     * Validate the add payroll request data.
     *
     * @param array $request The request data to be validated.
     * @return array|bool Returns an array with 'error' as key and validation error messages as value if validation fails.
     *                   Returns true if validation passes.
     */
    private function validateAddRequest($request): array|bool
    {
        $validator = Validator::make($request, $this->addValidation());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }

        return true;
    }

    /**
     * Validate the import payroll request data.
     *
     * @param array $request The request data to be validated.
     * @return array|bool Returns an array with 'error' as key and validation error messages as value if validation fails.
     *                   Returns true if validation passes.
     */
    private function validateImportRequest($request): array|bool
    {
        $validator = Validator::make($request->toArray(), $this->importValidation());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }

        return true;
    }

    /**
     * Validate the update payroll request data.
     *
     * @param array $request The request data to be validated.
     * @return array|bool Returns an array with 'error' as key and validation error messages as value if validation fails.
     *                   Returns true if validation passes.
     */
    private function validateUpdateRequest($request): array|bool
    {
        $validator = Validator::make($request, $this->updateValidation());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }

        return true;
    }

    /**
     * Validate the given request data.
     *
     * @param array $request The request data to be validated.
     * @return array|bool Returns an array with 'error' as key and validation error messages as value if validation fails.
     *                   Returns true if validation passes.
     */
    private function validateUploadTimesheetRequest($request): array|bool
    {
        $validator = Validator::make($request->toArray(), $this->uploadTimesheetValidation());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }

        return true;
    }

    /**
     * Show the project detail
     *
     * @param $request
     *        project_id (int) ID of the project
     *
     * @return mixed  Returns the total management payroll project record
     */
    public function projectDetails($request): mixed
    {
        return $this->totalManagementProject
            ->leftJoin('worker_employment', function($query) {
                $query->on('worker_employment.project_id','=','total_management_project.id')
                ->where('worker_employment.service_type', Config::get('services.WORKER_MODULE_TYPE')[1])
                ->where('worker_employment.transfer_flag', self::DEFAULT_TRANSFER_FLAG)
                ->whereNull('worker_employment.remove_date');
            })
            ->leftJoin('workers', function($query) {
                $query->on('workers.id','=','worker_employment.worker_id')
                ->whereIN('workers.total_management_status', Config::get('services.TOTAL_MANAGEMENT_WORKER_STATUS'));
            })
            ->where('total_management_project.id',$request['project_id'])
            ->select(DB::raw('COUNT(DISTINCT workers.id) as workers'), 'worker_employment.project_id', 'total_management_project.name')
                ->groupBy('worker_employment.project_id', 'total_management_project.name')
                ->get();
    }
    /**
     * Get a list of workers for the specified project
     *
     * @param $request
     *        project_id (int) ID of the project
     *        search (string) search parameter
     *        month (int) month of the payroll
     *        year (int) year of the payroll
     *
     * @return mixed Returns The paginated list of payroll
     *
     * @see applyCondition()
     * @see applySearchFilter()
     * @see ListSelectColumns()
     *
     */
    public function list($request): mixed
    {
        $data = $this->workers
            ->leftJoin('worker_visa', 'worker_visa.worker_id', 'workers.id')
            ->leftJoin('worker_employment', function($query) {
                $query->on('worker_employment.worker_id','=','workers.id');
            })
            ->leftJoin('worker_bank_details', function($query) {
                $query->on('worker_bank_details.worker_id','=','workers.id')
                    ->whereRaw('worker_bank_details.id IN (select MIN(WORKER_BANK.id) from worker_bank_details as WORKER_BANK JOIN workers as WORKER ON WORKER.id = WORKER_BANK.worker_id group by WORKER.id)');
            })
            ->leftJoin('total_management_payroll', function($query) use ($request) {
                $projectId = $request['project_id'] ?? '';
                $query->on('total_management_payroll.worker_id','=','worker_employment.worker_id');
                if(!empty($projectId) && empty($request['month']) && empty($request['year'])){
                $query->whereRaw('total_management_payroll.id IN (select MAX(TMPAY.id) from total_management_payroll as TMPAY JOIN workers as WORKER ON WORKER.id = TMPAY.worker_id group by WORKER.id)');
                }
            })
            ->leftJoin('total_management_project', 'total_management_project.id', 'worker_employment.project_id');
        $data = $this->applyCondition($request,$data);
        $data = $this->applySearchFilter($request,$data);
        $data = $this->ListSelectColumns($data)
                    ->orderBy('workers.created_at','DESC')
                    ->paginate(Config::get('services.paginate_row'));
        return $data;
    }

    /**
     * Apply condition to the query builder based on user data
     *
     * @param array $request The user data
     *        project_id (int) ID of the project
     *
     * @return $data Returns the query builder object with the applied condition
     */
    private function applyCondition($request,$data)
    {
        return $data->where('worker_employment.project_id', $request['project_id'])
                    ->where('worker_employment.service_type', Config::get('services.WORKER_MODULE_TYPE')[1]);
    }

    /**
     * Apply search filter to the query builder based on user data
     *
     * @param array $request The user data
     *        search (string) search parameter
     *        month (int) Payroll month
     *        year (int) Payroll year
     *        project_id (int) ID of the project
     *
     * @return $data Returns the query builder object with the applied search filter
     */
    private function applySearchFilter($request,$data)
    {
        return $data->where(function ($query) use ($request) {
            $search = $request['search'] ?? '';
            $month = $request['month'] ?? '';
            $year = $request['year'] ?? '';
            $projectId = $request['project_id'] ?? '';

            if (!empty($search)) {
                $query->where('workers.name', 'like', "%{$search}%")
                ->orWhere('workers.passport_number', 'like', '%'.$search.'%')
                ->orWhere('worker_employment.department', 'like', '%'.$search.'%');
            }
            if (!empty($month)) {
                $query->where('total_management_payroll.month', $month);
            }
            if (!empty($year)) {
                $query->where('total_management_payroll.year', $year);
            }
            if (!empty($projectId) && empty($month) && empty($year)){
                $query->whereNull('worker_employment.work_end_date');
                $query->whereNull('worker_employment.remove_date');
                $query->whereIn('workers.total_management_status', Config::get('services.TOTAL_MANAGEMENT_WORKER_STATUS'));
            }
        });
    }

    /**
     * Select the worker payroll columns from the list payroll query.
     *
     * @return $data The modified instance of the class.
     */
    private function listSelectColumns($data)
    {
        return $data->select('workers.id', 'workers.name', 'workers.passport_number', 'worker_bank_details.bank_name', 'worker_bank_details.account_number', 'worker_bank_details.socso_number', 'worker_employment.department', 'total_management_payroll.id as payroll_id', 'total_management_payroll.month', 'total_management_payroll.year', 'total_management_payroll.basic_salary', 'total_management_payroll.ot_1_5', 'total_management_payroll.ot_2_0', 'total_management_payroll.ot_3_0', 'total_management_payroll.ph', 'total_management_payroll.rest_day', 'total_management_payroll.deduction_advance', 'total_management_payroll.deduction_accommodation', 'total_management_payroll.annual_leave', 'total_management_payroll.medical_leave', 'total_management_payroll.hospitalisation_leave', 'total_management_payroll.amount', 'total_management_payroll.no_of_workingdays', 'total_management_payroll.normalday_ot_1_5', 'total_management_payroll.ot_1_5_hrs_amount', 'total_management_payroll.restday_daily_salary_rate', 'total_management_payroll.hrs_ot_2_0', 'total_management_payroll.ot_2_0_hrs_amount', 'total_management_payroll.public_holiday_ot_3_0', 'total_management_payroll.deduction_hostel', 'total_management_payroll.sosco_deduction', 'total_management_payroll.sosco_contribution', 'workers.created_at')
                    ->distinct('workers.id');
    }

    /**
     * Export worker payroll data based on specified project.
     *
     * @param $request
     *        project_id (int) ID of the project
     *        search (string) search parameter
     *        month (int) month of the payroll
     *        year (int) year of the payroll
     *
     * @return mixed Returns The list of payroll
     *
     * @see applyCondition()
     * @see applySearchFilter()
     * @see exportSelectColumns()
     *
     */
    public function export($request): mixed
    {
        $data = $this->workers
            ->leftJoin('worker_visa', 'worker_visa.worker_id', 'workers.id')
            ->leftJoin('worker_employment', function($query) {
                $query->on('worker_employment.worker_id','=','workers.id');
            })
            ->leftJoin('worker_bank_details', function($query) {
                $query->on('worker_bank_details.worker_id','=','workers.id')
                    ->whereRaw('worker_bank_details.id IN (select MIN(WORKER_BANK.id) from worker_bank_details as WORKER_BANK JOIN workers as WORKER ON WORKER.id = WORKER_BANK.worker_id group by WORKER.id)');
            })
            ->leftJoin('total_management_payroll', function($query) use ($request) {
                $projectId = $request['project_id'] ?? '';
                $query->on('total_management_payroll.worker_id','=','worker_employment.worker_id');
                if(!empty($projectId) && empty($request['month']) && empty($request['year'])){
                $query->whereRaw('total_management_payroll.id IN (select MAX(TMPAY.id) from total_management_payroll as TMPAY JOIN workers as WORKER ON WORKER.id = TMPAY.worker_id group by WORKER.id)');
                }
            })
            ->leftJoin('total_management_project', 'total_management_project.id', 'worker_employment.project_id')
            ->join('total_management_applications', function ($join) use ($request) {
                $join->on('total_management_applications.id', '=', 'total_management_project.application_id')
                    ->whereIn('total_management_applications.company_id', $request['company_id']);
            });
        $data = $this->applyCondition($request,$data);
        $data = $this->applySearchFilter($request,$data);
        $data = $this->exportSelectColumns($data)
                     ->orderBy('workers.created_at','DESC')->get();
        return $data;
    }

    /**
     * Select the worker payroll columns from the export payroll query.
     *
     * @return $data The modified instance of the class.
     */
    private function exportSelectColumns($data)
    {
        return $data->select('workers.id', 'workers.name', 'worker_bank_details.account_number', 'workers.passport_number', 'worker_employment.department', 'total_management_payroll.month', 'total_management_payroll.year', 'total_management_payroll.basic_salary', 'total_management_payroll.ot_1_5', 'total_management_payroll.ot_2_0', 'total_management_payroll.ot_3_0', 'total_management_payroll.ph', 'total_management_payroll.rest_day', 'total_management_payroll.deduction_advance', 'total_management_payroll.deduction_accommodation', 'total_management_payroll.annual_leave', 'total_management_payroll.medical_leave', 'total_management_payroll.hospitalisation_leave', 'total_management_payroll.amount', 'total_management_payroll.no_of_workingdays', 'total_management_payroll.normalday_ot_1_5', 'total_management_payroll.ot_1_5_hrs_amount', 'total_management_payroll.restday_daily_salary_rate', 'total_management_payroll.hrs_ot_2_0', 'total_management_payroll.ot_2_0_hrs_amount', 'total_management_payroll.public_holiday_ot_3_0', 'total_management_payroll.deduction_hostel', 'total_management_payroll.sosco_deduction', 'total_management_payroll.sosco_contribution', 'workers.created_at')
        ->distinct('workers.id');
    }

    /**
     * Show the details of a total management payroll record
     *
     * @param $request
     *        id (int) ID of the payroll
     *
     * @return mixed Returns the total management payroll record
     */
    public function show($request): mixed
    {
        $data = $this->totalManagementPayroll
            ->leftJoin('total_management_project', 'total_management_project.id', 'total_management_payroll.project_id')
            ->leftJoin('worker_employment', function($query) {
                $query->on('worker_employment.worker_id','=','total_management_payroll.worker_id')
                    ->on('worker_employment.project_id','=','total_management_payroll.project_id')
                    ->where('worker_employment.service_type', Config::get('services.WORKER_MODULE_TYPE')[1]);
            })
            ->leftJoin('workers', 'workers.id', 'total_management_payroll.worker_id')
            ->leftJoin('worker_visa', 'worker_visa.worker_id', 'workers.id')
            ->leftJoin('worker_bank_details', function($query) {
                $query->on('worker_bank_details.worker_id','=','workers.id')
                    ->whereRaw('worker_bank_details.id IN (select MIN(WORKER_BANK.id) from worker_bank_details as WORKER_BANK JOIN workers as WORKER ON WORKER.id = WORKER_BANK.worker_id group by WORKER.id)');
            })
            ->join('total_management_applications', function ($join) use ($request) {
                $join->on('total_management_applications.id', '=', 'total_management_project.application_id')
                     ->whereIn('total_management_applications.company_id', $request['company_id']);
            });
        $data = $this->showApplyCondition($request,$data);
        $data = $this->showSelectColumns($data)->get();
        return $data;
    }

    /**
     * Apply condition to the query builder based on user data
     *
     * @param array $request The user data
     *        id (int) ID of the payroll
     *
     * @return $data Returns the query builder object with the applied condition
     */
    private function showApplyCondition($request,$data)
    {
        return $data->where('total_management_payroll.id', $request['id']);
    }

	/**
     * Select the worker payroll columns from the show payroll query.
     *
     * @return $data The modified instance of the class.
     */
    private function showSelectColumns($data)
    {
        return $data->select('workers.id', 'workers.name', 'worker_bank_details.account_number', 'worker_bank_details.account_number', 'worker_bank_details.socso_number', 'workers.passport_number', 'worker_employment.department', 'total_management_payroll.month', 'total_management_payroll.year', 'total_management_payroll.basic_salary', 'total_management_payroll.ot_1_5', 'total_management_payroll.ot_2_0', 'total_management_payroll.ot_3_0', 'total_management_payroll.ph', 'total_management_payroll.rest_day', 'total_management_payroll.deduction_advance', 'total_management_payroll.deduction_accommodation', 'total_management_payroll.annual_leave', 'total_management_payroll.medical_leave', 'total_management_payroll.hospitalisation_leave', 'total_management_payroll.amount', 'total_management_payroll.no_of_workingdays', 'total_management_payroll.normalday_ot_1_5', 'total_management_payroll.ot_1_5_hrs_amount', 'total_management_payroll.restday_daily_salary_rate', 'total_management_payroll.hrs_ot_2_0', 'total_management_payroll.ot_2_0_hrs_amount', 'total_management_payroll.public_holiday_ot_3_0', 'total_management_payroll.deduction_hostel', 'total_management_payroll.sosco_deduction', 'total_management_payroll.sosco_contribution')
        ->distinct('workers.id','total_management_payroll.id');
    }

    /**
     * Import payroll data from a file
     *
     * @param $request
     *        project_id (int) ID of the project
     *
     * @return bool|array Returns true if the import is successful. Returns an error array if validation fails or any error occurs during the import process.
     *
     * @see validateImportRequest()
     * @see createPayrollBulkUpload()
     *
     */
    public function import($request, $file): mixed
    {
        $validationResult = $this->validateImportRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }

        $params = $request->all();
        $user = $this->getAuthenticatedUser();
        $params['created_by'] = $user['id'];
        $params['modified_by'] = $user['id'];
        $params['company_id'] = $user['company_id'];

        $payrollBulkUpload = $this->createPayrollBulkUpload($params);

        $rows = Excel::toArray(new PayrollImport($params, $payrollBulkUpload), $file);

        $this->payrollBulkUpload->where('id', $payrollBulkUpload->id)
        ->update(['actual_row_count' => count($rows[0])]);

        Excel::import(new PayrollImport($params, $payrollBulkUpload), $file);

        return true;
    }
    /**
     * create payroll bulk upload.
     *
     * @param array $request
     *              project_id (int) ID of the project
     *
     * @return mixed  Returns the created bulk upload record.
     */
    private function createPayrollBulkUpload($request): mixed
    {
        return $payrollBulkUpload = $this->payrollBulkUpload->create([
            'project_id' => $request['project_id'] ?? '',
            'name' => self::PAYROLL_BULK_UPLOAD_TYPE,
            'type' => self::PAYROLL_BULK_UPLOAD_TYPE,
            'company_id' => $request['company_id'],
            'created_by' => $request['created_by'],
            'modified_by' => $request['created_by']
        ]);
    }
    /**
     * Add a new entry to the total management payroll.
     *
     * @param $request
     *
     * @return bool|array Returns true if the create is successful. Returns an error array if validation fails or any error occurs during the create process.
     *
     * @see validateAddRequest()
     * @see getPayrollRecord()
     * @see createTotalManagementPayroll()
     *
     */
    public function add($request): bool|array
    {
        $validationResult = $this->validateAddRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }

        $totalManagementPayroll = $this->getPayrollRecord($request);

        if(is_null($totalManagementPayroll)){
            $this->createTotalManagementPayroll($request);
            return true;
        }else{
            return false;
        }
    }
    /**
     * Retrieve payroll record.
     *
     * @param array request
     *              worker_id (int) ID of the worker
     *              project_id (int) Id of the project
     *              month (int) month of the payroll
     *              year (int) year of the payroll
     *
     * @return mixed Returns the payroll record
     */
    private function getPayrollRecord($request)
    {
        return $this->totalManagementPayroll->where([
            ['worker_id', $request['worker_id']],
            ['project_id', $request['project_id']],
            ['month', $request['month']],
            ['year', $request['year']],
        ])->first(['id', 'worker_id', 'project_id', 'month', 'year']);
    }
    /**
     * create total management payroll record.
     *
     * @param array $request
     *              worker_id (int) ID of the worker
     *              project_id (int) Id of the project
     *              month (int) month of the payroll
     *              year (int) year of the payroll
     *              basic_salary (float) salary of the worker
     *              ot_1_5 (float) amount of OT @1.5
     *              ot_2_0 (float) amount of OT @2.0
     *              ot_3_0 (float) amount of OT @3.0
     *              ph (float) amount of PH
     *              rest_day (int) rest day
     *              deduction_advance (float) deduction advance amount
     *              deduction_accommodation (float) accommodation deduction amount
     *              annual_leave (int) annual leave
     *              medical_leave (int) medical leave
     *              hospitalisation_leave (int) hospitalisation leave
     *              amount (float) amount
     *              no_of_workingdays (int) no.of working days/month
     *              ot_1_5_hrs_amount (float) OT @1.5 Hrs amount
     *              restday_daily_salary_rate (float) rest day daily salary rate
     *              hrs_ot_2_0 (float) OT @2.0	Hrs
     *              ot_2_0_hrs_amount (float) OT @2.0 Hrs amount
     *              public_holiday_ot_3_0 (float) public holiday OT @3.0
     *              deduction_hostel (float) hostal deduction amount
     *              sosco_deduction (float) sosco deduction amount
     *              sosco_contribution (float) sosco contribution amount
     *              created_by ID of the user who created payroll
     *
     * @return mixed Returns the created payroll record.
     */
    private function createTotalManagementPayroll($request): mixed
    {
        return $this->totalManagementPayroll->create([
            'worker_id' => $request['worker_id'] ?? self::DEFAULT_VALUE,
            'project_id' => $request['project_id'] ?? self::DEFAULT_VALUE,
            'month' => $request['month'] ?? self::DEFAULT_VALUE,
            'year' => $request['year'] ?? self::DEFAULT_VALUE,
            'basic_salary' => $request['basic_salary'] ?? self::DEFAULT_VALUE,
            'ot_1_5' => $request['ot_1_5'] ?? self::DEFAULT_VALUE,
            'ot_2_0' => $request['ot_2_0'] ?? self::DEFAULT_VALUE,
            'ot_3_0' => $request['ot_3_0'] ?? self::DEFAULT_VALUE,
            'ph' => $request['ph'] ?? self::DEFAULT_VALUE,
            'rest_day' => $request['rest_day'] ?? self::DEFAULT_VALUE,
            'deduction_advance' => $request['deduction_advance'] ?? self::DEFAULT_VALUE,
            'deduction_accommodation' => $request['deduction_accommodation'] ?? self::DEFAULT_VALUE,
            'annual_leave' => $request['annual_leave'] ?? self::DEFAULT_VALUE,
            'medical_leave' => $request['medical_leave'] ?? self::DEFAULT_VALUE,
            'hospitalisation_leave' => $request['hospitalisation_leave'] ?? self::DEFAULT_VALUE,
            'amount' => $request['amount'] ?? self::DEFAULT_VALUE,
            'no_of_workingdays' => $request['no_of_workingdays'] ?? self::DEFAULT_VALUE,
            'normalday_ot_1_5' => $request['normalday_ot_1_5'] ?? self::DEFAULT_VALUE,
            'ot_1_5_hrs_amount' => $request['ot_1_5_hrs_amount'] ?? self::DEFAULT_VALUE,
            'restday_daily_salary_rate' => $request['restday_daily_salary_rate'] ?? self::DEFAULT_VALUE,
            'hrs_ot_2_0' => $request['hrs_ot_2_0'] ?? self::DEFAULT_VALUE,
            'ot_2_0_hrs_amount' => $request['ot_2_0_hrs_amount'] ?? self::DEFAULT_VALUE,
            'public_holiday_ot_3_0' => $request['public_holiday_ot_3_0'] ?? self::DEFAULT_VALUE,
            'deduction_hostel' => $request['deduction_hostel'] ?? self::DEFAULT_VALUE,
            'sosco_deduction' => $request['sosco_deduction'] ?? self::DEFAULT_VALUE,
            'sosco_contribution' => $request['sosco_contribution'] ?? self::DEFAULT_VALUE,
            'created_by' => $request['created_by'] ?? self::DEFAULT_VALUE,
            'modified_by' => $request['created_by'] ?? self::DEFAULT_VALUE
        ]);
    }
    /**
     * update the Total Management Payroll.
     *
     * @param $request The request object containing the payroll update data
     *
     * @return bool|array Returns true if the update is successful. Returns an error array if validation fails or any error occurs during the update process.
     *                    Returns self::ERROR_UNAUTHORIZED if the user access invalid payroll
     *
     * @see validateUpdateRequest()
     * @see getPayrollRecordToUpdate()
     * @see updatePayroll()
     *
     */
    public function update($request): bool|array
    {
        $validationResult = $this->validateUpdateRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }

        $totalManagementPayroll = $this->getPayrollRecordToUpdate($request);

        if(is_null($totalManagementPayroll)){
            return self::ERROR_UNAUTHORIZED;
        }
        $this->updatePayroll($totalManagementPayroll, $request);

        return true;
    }
    /**
     * Retrieve payroll record for update.
     *
     * @param array request
     *              id (int) ID of the payroll
     *              company_id (array) ID of the user company
     *
     * @return mixed Returns the payroll record.
     */
    private function getPayrollRecordToUpdate($request)
    {
        return $this->totalManagementPayroll
        ->join('total_management_project', 'total_management_project.id', 'total_management_payroll.project_id')
        ->join('total_management_applications', function ($join) use ($request) {
            $join->on('total_management_applications.id', '=', 'total_management_project.application_id')
                 ->whereIn('total_management_applications.company_id', $request['company_id']);
        })->select('total_management_payroll.id', 'total_management_payroll.worker_id', 'total_management_payroll.project_id', 'total_management_payroll.month', 'total_management_payroll.year', 'total_management_payroll.basic_salary', 'total_management_payroll.ot_1_5', 'total_management_payroll.ot_2_0', 'total_management_payroll.ot_3_0', 'total_management_payroll.ph', 'total_management_payroll.rest_day', 'total_management_payroll.deduction_advance', 'total_management_payroll.deduction_accommodation', 'total_management_payroll.annual_leave', 'total_management_payroll.medical_leave', 'total_management_payroll.hospitalisation_leave', 'total_management_payroll.amount', 'total_management_payroll.no_of_workingdays', 'total_management_payroll.normalday_ot_1_5', 'total_management_payroll.ot_1_5_hrs_amount', 'total_management_payroll.restday_daily_salary_rate', 'total_management_payroll.hrs_ot_2_0', 'total_management_payroll.ot_2_0_hrs_amount', 'total_management_payroll.public_holiday_ot_3_0', 'total_management_payroll.deduction_hostel', 'total_management_payroll.created_by', 'total_management_payroll.modified_by', 'total_management_payroll.sosco_deduction', 'total_management_payroll.sosco_contribution', 'total_management_payroll.created_at', 'total_management_payroll.updated_at', 'total_management_payroll.deleted_at')->find($request['id']);
    }
    /**
     * Update payroll based on the provided request.
     *
     * @param mixed $totalManagementPayroll
     * @param array $request
     *              worker_id (int) ID of the worker
     *              project_id (int) Id of the project
     *              month (int) month of the payroll
     *              year (int) year of the payroll
     *              basic_salary (float) salary of the worker
     *              ot_1_5 (float) amount of OT @1.5
     *              ot_2_0 (float) amount of OT @2.0
     *              ot_3_0 (float) amount of OT @3.0
     *              ph (float) amount of PH
     *              rest_day (int) rest day
     *              deduction_advance (float) deduction advance amount
     *              deduction_accommodation (float) accommodation deduction amount
     *              annual_leave (int) annual leave
     *              medical_leave (int) medical leave
     *              hospitalisation_leave (int) hospitalisation leave
     *              amount (float) amount
     *              no_of_workingdays (int) no.of working days/month
     *              ot_1_5_hrs_amount (float) OT @1.5 Hrs amount
     *              restday_daily_salary_rate (float) rest day daily salary rate
     *              hrs_ot_2_0 (float) OT @2.0	Hrs
     *              ot_2_0_hrs_amount (float) OT @2.0 Hrs amount
     *              public_holiday_ot_3_0 (float) public holiday OT @3.0
     *              deduction_hostel (float) hostal deduction amount
     *              sosco_deduction (float) sosco deduction amount
     *              sosco_contribution (float) sosco contribution amount
     *              created_by ID of the user who created payroll
     * 
     * @return void
     *
     */
    private function updatePayroll($totalManagementPayroll, $request)
    {
        $totalManagementPayroll->basic_salary =  $request['basic_salary'] ?? $totalManagementPayroll->basic_salary;
        $totalManagementPayroll->ot_1_5 =  $request['ot_1_5'] ?? $totalManagementPayroll->ot_1_5;
        $totalManagementPayroll->ot_2_0 =  $request['ot_2_0'] ?? $totalManagementPayroll->ot_2_0;
        $totalManagementPayroll->ot_3_0 =  $request['ot_3_0'] ?? $totalManagementPayroll->ot_3_0;
        $totalManagementPayroll->ph =  $request['ph'] ?? $totalManagementPayroll->ph;
        $totalManagementPayroll->rest_day =  $request['rest_day'] ?? $totalManagementPayroll->rest_day;
        $totalManagementPayroll->deduction_advance =  $request['deduction_advance'] ?? $totalManagementPayroll->deduction_advance;
        $totalManagementPayroll->deduction_accommodation =  $request['deduction_accommodation'] ?? $totalManagementPayroll->deduction_accommodation;
        $totalManagementPayroll->annual_leave =  $request['annual_leave'] ?? $totalManagementPayroll->annual_leave;
        $totalManagementPayroll->medical_leave =  $request['medical_leave'] ?? $totalManagementPayroll->medical_leave;
        $totalManagementPayroll->hospitalisation_leave =  $request['hospitalisation_leave'] ?? $totalManagementPayroll->hospitalisation_leave;
        $totalManagementPayroll->amount =  $request['amount'] ?? $totalManagementPayroll->amount;
        $totalManagementPayroll->no_of_workingdays =  $request['no_of_workingdays'] ?? $totalManagementPayroll->no_of_workingdays;
        $totalManagementPayroll->normalday_ot_1_5 =  $request['normalday_ot_1_5'] ?? $totalManagementPayroll->normalday_ot_1_5;
        $totalManagementPayroll->ot_1_5_hrs_amount =  $request['ot_1_5_hrs_amount'] ?? $totalManagementPayroll->ot_1_5_hrs_amount;
        $totalManagementPayroll->restday_daily_salary_rate =  $request['restday_daily_salary_rate'] ?? $totalManagementPayroll->restday_daily_salary_rate;
        $totalManagementPayroll->hrs_ot_2_0 =  $request['hrs_ot_2_0'] ?? $totalManagementPayroll->hrs_ot_2_0;
        $totalManagementPayroll->ot_2_0_hrs_amount =  $request['ot_2_0_hrs_amount'] ?? $totalManagementPayroll->ot_2_0_hrs_amount;
        $totalManagementPayroll->public_holiday_ot_3_0 =  $request['public_holiday_ot_3_0'] ?? $totalManagementPayroll->public_holiday_ot_3_0;
        $totalManagementPayroll->deduction_hostel =  $request['deduction_hostel'] ?? $totalManagementPayroll->deduction_hostel;
        $totalManagementPayroll->sosco_deduction =  $request['sosco_deduction'] ?? $totalManagementPayroll->sosco_deduction;
        $totalManagementPayroll->sosco_contribution =  $request['sosco_contribution'] ?? $totalManagementPayroll->sosco_contribution;
        $totalManagementPayroll->modified_by =  $request['modified_by'] ?? $totalManagementPayroll->modified_by;
        $totalManagementPayroll->save();
    }
    /**
     * list timesheet
     *
     * @param $request
     *        project_id (int) ID of the project
     *
     * @return mixed Returns The paginated list of timesheet
     */
    public function listTimesheet($request): mixed
    {
        return $this->totalManagementPayrollAttachments
            ->where('file_id', $request['project_id'])
            ->select('id', 'month', 'year', 'file_id', 'file_name', 'file_type', 'file_url', 'created_at')
            ->distinct('id')
            ->orderBy('id','DESC')
            ->paginate(Config::get('services.paginate_row'));
    }
    /**
     * view timesheet
     *
     * @param $request
     *        project_id (int) ID of the project
     *        month (int) month of the payroll
     *        year (int) year of the payroll
     *
     * @return mixed Returns the timesheet record
     */
    public function viewTimesheet($request): mixed
    {
        return $this->totalManagementPayrollAttachments
        ->where([
            'file_id' => $request['project_id'],
            'month' => $request['month'],
            'year' => $request['year']
        ])
        ->select('id', 'month', 'year', 'file_id', 'file_name', 'file_type', 'file_url', 'created_at')
        ->get();
    }
    /**
     * upload Timesheet
     *
     * @param $request The request object containing the payroll timesheet data
     *
     * @return bool|array Returns true if the upload is successful. Returns an error array if validation fails or any error occurs during the upload process.
     *                    Returns self::ERROR_EXISTS if attachment already exists
     *
     * @see validateUploadTimesheetRequest()
     * @see getPayrollAttachments()
     * @see uploadPayrollTimesheet()
     *
     */
    public function uploadTimesheet($request): bool|array
    {
        $validationResult = $this->validateUploadTimesheetRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }

        $request = $this->enrichRequestWithUserDetails($request);

        $totalManagementPayrollAttachments = $this->getPayrollAttachments($request);

        if(is_null($totalManagementPayrollAttachments)){
            $this->uploadPayrollTimesheet($request);
            return true;
        }else{
            return self::ERROR_EXISTS;
        }
        return false;
    }
    /**
     * Retrieve attachments.
     *
     * @param array request
     *              project_id (int) ID of the project
     *              month (int) month of the project
     *              year (int) month of the year
     *
     *
     * @return mixed Returns the attachment record
     */
    private function getPayrollAttachments($request)
    {
        return $this->totalManagementPayrollAttachments
        ->where([
            'file_id' => $request['project_id'],
            'month' => $request['month'],
            'year' => $request['year']
        ])->first(['id', 'month', 'year', 'file_id', 'file_name', 'file_type', 'file_url', 'created_at']);
    }
    /**
     * Upload attachment of payroll.
     *
     * @param array $request
     *              project_id (int) ID of the project
     *              month (int) month of the project
     *              year (int) month of the year
     *
     * @return void
     */
    private function uploadPayrollTimesheet($request): void
    {
        if (request()->hasFile('attachment')){
            foreach($request->file('attachment') as $file){
                $fileName = $file->getClientOriginalName();
                $filePath = '/totalManagement/payroll/timesheet/' . $fileName;
                $linode = $this->storage::disk('linode');
                $linode->put($filePath, file_get_contents($file));
                $fileUrl = $this->storage::disk('linode')->url($filePath);

                $this->totalManagementPayrollAttachments::create([
                    'file_id' => $request['project_id'],
                    "month" => $request['month'] ?? self::DEFAULT_VALUE,
                    "year" => $request['year'] ?? self::DEFAULT_VALUE,
                    "file_name" => $fileName,
                    "file_type" => self::FILE_TYPE,
                    "file_url" =>  $fileUrl,
                    "created_by" =>  $request['created_by'] ?? self::DEFAULT_VALUE,
                    "modified_by" =>  $request['created_by'] ?? self::DEFAULT_VALUE
                ]);
            }
        }
    }

    /**
     * process the authorize Payroll
     *
     * @param $request The request object containing the authorize payroll data
     *
     * @return bool|array Returns true if the authorizePayroll is successful. Returns an error array if validation fails or any error occurs during the authorizePayroll process.
     *                    Returns self::ERROR_EXISTS if cost management already exists
     *                    Returns self::ERROR_NORECORDS if no workers found
     *
     * @see countTotalManagementCostManagement()
     * @see getPayrollWorkers()
     * @see createCostmanagementEntry()
     * @see createCostmanagementSocsoEntry()
     *
     */
    public function authorizePayroll($request): bool|array
    {
        $checkTotalManagementCostManagement = $this->countTotalManagementCostManagement($request);
        if($checkTotalManagementCostManagement > self::DEFAULT_VALUE) {
            return self::ERROR_EXISTS;
        }

        $payrollWorkers = $this->getPayrollWorkers($request);

        if(isset($payrollWorkers) && count($payrollWorkers) > self::DEFAULT_VALUE ){
            $user = JWTAuth::parseToken()->authenticate();
            foreach($payrollWorkers as $result){
                $this->createCostmanagementEntry($result,$request,$user);
                $this->createCostmanagementSocsoEntry($result,$request,$user);
            }
        } else {
            return self::ERROR_NO_RECORDS;
        }
        return true;
    }

    /**
     * count cost management specified project request data.
     *
     * @param array $request
     *              project_id (int) ID of the project
     *              company_id (array) ID of the user company
     *              month (int) month of the project
     *              year (int) year of the project
     *
     * @return mixed Returns the cost management count
     */
    private function countTotalManagementCostManagement($request)
    {
        return $this->totalManagementCostManagement
        ->join('total_management_project', 'total_management_project.id', 'total_management_cost_management.project_id')
        ->join('total_management_applications', function ($join) use ($request) {
            $join->on('total_management_applications.id', '=', 'total_management_project.application_id')
                ->whereIn('total_management_applications.company_id', $request['company_id']);
        })->where('project_id',$request['project_id'])->where('month',$request['month'])->where('year',$request['year'])->count();
    }
    /**
     * get payroll workers.
     *
     *@param array $request
     *              project_id (int) ID of the project
     *              month (int) month of the project
     *              year (int) year of the project
     *
     * @return mixed Returns the worker payroll data
     *
     * @see getPayrollWorkersApplyCondition()
     * @see getPayrollWorkersSelectColumns()
     *
     */
    private function getPayrollWorkers($request)
    {
        $data = $this->workers
        ->leftJoin('worker_employment', function($query) {
            $query->on('worker_employment.worker_id','=','workers.id');
        })
        ->leftJoin('total_management_payroll', function($query) use ($request) {
            $query->on('total_management_payroll.worker_id','=','workers.id');
            if(isset($request['project_id']) && !empty($request['project_id']) && empty($request['month']) && empty($request['year'])){
            $query->whereRaw('total_management_payroll.id IN (select MAX(TMPAY.id) from total_management_payroll as TMPAY JOIN workers as WORKER ON WORKER.id = TMPAY.worker_id group by WORKER.id)');
            }
        })
        ->leftJoin('total_management_project', function($query) {
            $query->on('total_management_payroll.project_id','=','total_management_project.id');
        })
        ->join('total_management_applications', function ($join) use ($request) {
            $join->on('total_management_applications.id', '=', 'total_management_project.application_id')
                ->whereIn('total_management_applications.company_id', $request['company_id']);
        });
        $data = $this->getPayrollWorkersApplyCondition($request,$data);
        $data = $this->getPayrollWorkersSelectColumns($data)
                        ->orderBy('workers.created_at','DESC')->get();
        return $data;
    }

    /**
     * Apply condition to the query builder based on user data
     *
     * @param array $request The user data
     *        project_id (int) ID of the project
     *        month (int) payroll month
     *        year (int) payroll year
     *
     * @return $data Returns the query builder object with the applied condition
     */
    private function getPayrollWorkersApplyCondition($request,$data)
    {
        return $data->where(function ($query) use ($request) {
            $month = $request['month'] ?? '';
            $year = $request['year'] ?? '';
            $projectId = $request['project_id'] ?? '';
            if (!empty($month)) {
                $query->where('total_management_payroll.month', $month);
            }
            if (!empty($year)) {
                $query->where('total_management_payroll.year', $year);
            }
            if (!empty($projectId) && empty($month) && empty($year)){
                $query->whereNull('worker_employment.work_end_date');
                $query->whereNull('worker_employment.remove_date');
            }
        });
    }

    /**
     * Select the worker payroll columns from the worker query.
     *
     * @return $data The modified instance of the class.
     */
    private function getPayrollWorkersSelectColumns($data)
    {
        return $data->select('workers.id as worker_id', 'workers.name', 'total_management_payroll.id as payroll_id', 'total_management_payroll.amount', 'total_management_payroll.sosco_contribution', 'total_management_project.application_id')
        ->distinct('workers.id');
    }

    /**
     * create Costmanagement Entry .
     *
     * @param array $result
     * @param array $request
     * @param array $user
     * @return void
     */
    private function createCostmanagementEntry($result,$request,$user): void
    {
        $this->totalManagementCostManagement->create([
            'application_id' => $result['application_id'],
            'project_id' => $request['project_id'],
            'title' => $result['name'],
            'type' => self::COSTMANAGEMENT_TYPE,
            'payment_reference_number' => self::DEFAULT_ACTIVE_VALUE,
            'payment_date' => Carbon::now(),
            'is_payroll' => self::DEFAULT_ACTIVE_VALUE,
            'quantity' => self::DEFAULT_ACTIVE_VALUE,
            'amount' => $result['amount'],
            'remarks' => $result['name'],
            'is_payroll' => self::DEFAULT_ACTIVE_VALUE,
            'payroll_id' => $result['payroll_id'],
            'month' => $request['month'],
            'year' => $request['year'],
            'created_by'    => $user['id'] ?? self::DEFAULT_VALUE,
            'modified_by'   => $user['id'] ?? self::DEFAULT_VALUE,
        ]);
    }
    /**
     * create Costmanagement socso Entry .
     *
     * @param array $result
     * @param array $request
     * @param array $user
     * @return void
     */
    private function createCostmanagementSocsoEntry($result,$request,$user): void
    {
        $this->totalManagementCostManagement->create([
            'application_id' => $result['application_id'],
            'project_id' => $request['project_id'],
            'title' => "SOCSO Contribution (" . $result['name'] . " )",
            'type' => self::COSTMANAGEMENT_TYPE,
            'payment_reference_number' => self::DEFAULT_ACTIVE_VALUE,
            'payment_date' => Carbon::now(),
            'is_payroll' => self::DEFAULT_ACTIVE_VALUE,
            'quantity' => self::DEFAULT_ACTIVE_VALUE,
            'amount' => $result['amount'],
            'remarks' => "SOCSO Contribution (" . $result['name'] . " )",
            'is_payroll' => self::DEFAULT_ACTIVE_VALUE,
            'payroll_id' => $result['payroll_id'],
            'month' => $request['month'],
            'year' => $request['year'],
            'created_by'    => $user['id'] ?? self::DEFAULT_VALUE,
            'modified_by'   => $user['id'] ?? self::DEFAULT_VALUE,
        ]);
    }
    /**
     * total management payroll import history
     *
     * @param $request
     * @return mixed
     *
     * @see enrichRequestWithUserDetails()
     *
     */
    public function importHistory($request): mixed
    {
        $request = $this->enrichRequestWithUserDetails($request);

        return $this->payrollBulkUpload
        ->select('id', 'actual_row_count', 'total_success', 'total_failure', 'process_status', 'created_at')
        ->where('process_status', 'Processed')
        ->whereNotNull('failure_case_url')
        ->whereIn('company_id', $request['company_id'])
        ->orderBy('id', 'desc')
        ->paginate(Config::get('services.paginate_row'));
    }

    /**
     * total management payroll import failure excel download
     *
     * @param $request The request data containing the params 
     *                 company_id (array) User company id
     *                 bulk_upload_id (int) upload id
     * 
     * @return array Returns the file_url with datas otherwise returns error message
     *               Returns ERROR_INVALID_USER - if the payroll bulk upload is not mapped with user company id and id.
     *               Returns ERROR_QUEUE - if the process status is not equal to Processed and file url is null
     */
    public function failureExport($request): array
    {
        $payrollBulkUpload = $this->payrollBulkUpload
                        ->where('company_id', $request['company_id'])
                        ->where('id', $request['bulk_upload_id'])
                        ->first();
        if(is_null($payrollBulkUpload)) {
            return self::ERROR_INVALID_USER;
        }
        if($payrollBulkUpload->process_status != 'Processed' || is_null($payrollBulkUpload->failure_case_url)) {
            return self::ERROR_QUEUE;
        }
        return [
            'file_url' => $payrollBulkUpload->failure_case_url
        ];
    }
    /**
     * total management payroll import failure case excel file creation
     *
     * @return bool
     *
     * @see updatePayrollBulkUploadStatus()
     * @see createPayrollFailureCasesDocument()
     *
     */
    public function prepareExcelForFailureCases(): bool
    {
        $ids = [];
        $bulkUploads = $this->getPayrollBulkUploadRows();
        foreach($bulkUploads as $bulkUpload) {
            if($bulkUpload['actual_row_count'] == ($bulkUpload['total_success'] + $bulkUpload['total_failure'])) {
                array_push($ids, $bulkUpload['id']);
            }
        }
        $this->updatePayrollBulkUploadStatus($ids);
        $this->createPayrollFailureCasesDocument($ids);
        return true;
    }
    /**
     * Get the payroll bulk upload rows.
     *
     * @return mixed
     */
    private function getPayrollBulkUploadRows(): mixed
    {
        return $this->payrollBulkUpload
        ->where( function ($query) {
            $query->whereNull('process_status')
            ->orWhereNull('failure_case_url');
        })
        ->select('id', 'total_records', 'total_success', 'total_failure', 'actual_row_count')
        ->get()->toArray();
    }

    /**
     * Update the status of payroll bulk upload rows.
     *
     * @param array $ids payroll bulk upload id's
     * 
     * @return void
     */
    private function updatePayrollBulkUploadStatus($ids)
    {
        $this->payrollBulkUpload->whereIn('id', $ids)->update(['process_status' => 'Processed']);
    }

    /**
     * create payroll failure cases document.
     *
     * @param array $ids payroll bulk upload id's
     * @return void
     */
    private function createPayrollFailureCasesDocument($ids)
    {
        foreach($ids as $id) {
            $fileName = "FailureCases" . $id . ".xlsx";
            $filePath = '/FailureCases/TotalManagement/' . $fileName;
            Excel::store(new PayrollFailureExport($id), $filePath, 'linode');
            $fileUrl = $this->storage::disk('linode')->url($filePath);
            $this->payrollBulkUpload->where('id', $id)->update(['failure_case_url' => $fileUrl]);
        }
    }

}
