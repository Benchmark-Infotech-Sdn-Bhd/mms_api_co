<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Config;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Workers;
use App\Models\EContractPayroll;
use App\Models\EContractPayrollAttachments;
use App\Models\EContractPayrollBulkUpload;
use App\Models\EContractCostManagement;
use App\Models\EContractProject;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\EContractPayrollImport;
use App\Exports\EContractPayrollFailureExport;
use App\Services\AuthServices;

class EContractPayrollServices
{
    public const SERVICE_TYPE_ECONTRACT = 'e-Contract';
    public const TRANSFER_FLAG_0 = 0;
    public const PAYROLL_UPLOAD_TYPE = 'Payroll Bulk Upload';
    public const PROCESS_STATUS_PROCESSED = 'Processed';
    public const PAYROLL_TYPE = 'Payroll';
    public const FILE_TYPE_TIMESHEET = 'Timesheet';
    public const SOCSO_CONTRIBUTION = 'SOCSO Contribution';
    public const DEFAULT_INTEGER_VALUE_ZERO = 0;
    public const DEFAULT_INTEGER_VALUE_ONE = 1;
    public const ECONTRACT_COST_MANAGEMENT_0 = 0;
    public const PAYROLL_WORKERS_0 = 0;

    /**
     * @var Workers
     */
    private Workers $workers;

    /**
     * @var EContractPayroll
     */
    private EContractPayroll $eContractPayroll;

    /**
     * @var EContractPayrollAttachments
     */
    private EContractPayrollAttachments $eContractPayrollAttachments;

    /**
     * @var EContractPayrollBulkUpload
     */
    private EContractPayrollBulkUpload $eContractPayrollBulkUpload;

    /**
     * @var EContractCostManagement
     */
    private EContractCostManagement $eContractCostManagement;

    /**
     * @var Storage
     */
    private Storage $storage;

    /**
     * @var EContractProject
     */
    private EContractProject $eContractProject;

    /**
     * @var AuthServices
     */
    private AuthServices $authServices;

    /**
     * Constructs a new instance of the class.
     * 
     * @param Workers $workers The workers object.
     * @param EContractPayroll $eContractPayroll The e-contract payroll object.
     * @param EContractPayrollAttachments $eContractPayrollAttachments The e-contract payroll attachments object.
     * @param Storage $storage The storage object.
     * @param EContractPayrollBulkUpload $eContractPayrollBulkUpload The e-contract payroll bulk upload object.
     * @param EContractCostManagement $eContractCostManagement The e-contractcost management object.
     * @param EContractProject $eContractProject The e-contract project object.
     * @param AuthServices $authServices The auth services object.
     */
    public function __construct(
        Workers $workers, 
        EContractPayroll $eContractPayroll, 
        EContractPayrollAttachments $eContractPayrollAttachments, 
        Storage $storage, 
        EContractPayrollBulkUpload $eContractPayrollBulkUpload, 
        EContractCostManagement $eContractCostManagement, 
        EContractProject $eContractProject, 
        AuthServices $authServices
    )
    {
        $this->workers = $workers;
        $this->eContractPayroll = $eContractPayroll;
        $this->eContractPayrollAttachments = $eContractPayrollAttachments;
        $this->storage = $storage;
        $this->eContractPayrollBulkUpload = $eContractPayrollBulkUpload;
        $this->eContractCostManagement = $eContractCostManagement;
        $this->eContractProject = $eContractProject;
        $this->authServices = $authServices;
    }

    /**
     * Creates the validation rules for create a new e-contract payroll.
     *
     * @return array The array containing the validation rules.
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
     * Creates the validation rules for updating the e-contract payroll.
     *
     * @return array The array containing the validation rules.
     */
    public function updateValidation(): array
    {
        return [
            'id' => 'required'
        ];
    }

    /**
     * Creates the validation rules for updating the timesheet.
     *
     * @return array The array containing the validation rules.
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
     * Creates the validation rules for import the e-contract payroll.
     *
     * @return array The array containing the validation rules.
     */
    public function importValidation(): array
    {
        return [
            'project_id' => 'required'
        ];
    }

    /**
     * Returns a project details of workers based on the given search request.
     * 
     * @param array $request The search request parameters and project id.
     * @return mixed Returns a project details of workers.
     */  
    public function projectDetails($request): mixed
    {
        $user = JWTAuth::parseToken()->authenticate();
        return $this->workers
            ->leftJoin('worker_employment', 'worker_employment.worker_id','=','workers.id')
            ->leftJoin('e-contract_project', 'e-contract_project.id', '=', 'worker_employment.project_id')
            ->where('worker_employment.project_id', $request['project_id']) 
            ->where('worker_employment.service_type', self::SERVICE_TYPE_ECONTRACT)
            ->where('worker_employment.transfer_flag', self::TRANSFER_FLAG_0)
            ->whereNull('worker_employment.remove_date')
            ->whereIn('workers.econtract_status', Config::get('services.ECONTRACT_WORKER_STATUS'))
            ->where('workers.company_id', $user['company_id'])
            ->select(DB::raw('COUNT(DISTINCT workers.id) as workers'), 'worker_employment.project_id', 'e-contract_project.name')
            ->groupBy('worker_employment.project_id', 'e-contract_project.name')
            ->distinct('workers.id')
            ->get();
    }

    /**
     * Returns a paginated list of workers based on the given search request.
     * 
     * @param array $request The search request parameters.
     * @return mixed Returns a paginated list of workers with related visa and employment and bank details and payroll and project.
     */
    public function list($request): mixed
    {
        $user = JWTAuth::parseToken()->authenticate();
        $request['company_id'] = $user['company_id'];
        return $this->getWorkersQuery($request)
        ->select('workers.id', 'workers.name', 'workers.passport_number', 'worker_bank_details.bank_name', 'worker_bank_details.account_number', 'worker_bank_details.socso_number', 'worker_employment.department', 'e-contract_payroll.id as payroll_id', 'e-contract_payroll.month', 'e-contract_payroll.year', 'e-contract_payroll.basic_salary', 'e-contract_payroll.ot_1_5', 'e-contract_payroll.ot_2_0', 'e-contract_payroll.ot_3_0', 'e-contract_payroll.ph', 'e-contract_payroll.rest_day', 'e-contract_payroll.deduction_advance', 'e-contract_payroll.deduction_accommodation', 'e-contract_payroll.annual_leave', 'e-contract_payroll.medical_leave', 'e-contract_payroll.hospitalisation_leave', 'e-contract_payroll.amount', 'e-contract_payroll.no_of_workingdays', 'e-contract_payroll.normalday_ot_1_5', 'e-contract_payroll.ot_1_5_hrs_amount', 'e-contract_payroll.restday_daily_salary_rate', 'e-contract_payroll.hrs_ot_2_0', 'e-contract_payroll.ot_2_0_hrs_amount', 'e-contract_payroll.public_holiday_ot_3_0', 'e-contract_payroll.deduction_hostel', 'e-contract_payroll.sosco_deduction', 'e-contract_payroll.sosco_contribution', 'workers.created_at')
        ->paginate(Config::get('services.paginate_row'));
    }

    /**
     * Returns a export list of workers based on the given search request.
     * 
     * @param array $request The search request parameters.
     * @return mixed Returns a export list of workers with related visa and employment and bank details and payroll and project.
     */ 
    public function export($request): mixed
    {
        $user = JWTAuth::parseToken()->authenticate();
        $request['company_id'] = $user['company_id'];
        return $this->getWorkersQuery($request)
        ->select('workers.id', 'workers.name', 'worker_bank_details.account_number', 'workers.passport_number', 'worker_employment.department', 'e-contract_payroll.month', 'e-contract_payroll.year', 'e-contract_payroll.basic_salary', 'e-contract_payroll.ot_1_5', 'e-contract_payroll.ot_2_0', 'e-contract_payroll.ot_3_0', 'e-contract_payroll.ph', 'e-contract_payroll.rest_day', 'e-contract_payroll.deduction_advance', 'e-contract_payroll.deduction_accommodation', 'e-contract_payroll.annual_leave', 'e-contract_payroll.medical_leave', 'e-contract_payroll.hospitalisation_leave', 'e-contract_payroll.amount', 'e-contract_payroll.no_of_workingdays', 'e-contract_payroll.normalday_ot_1_5', 'e-contract_payroll.ot_1_5_hrs_amount', 'e-contract_payroll.restday_daily_salary_rate', 'e-contract_payroll.hrs_ot_2_0', 'e-contract_payroll.ot_2_0_hrs_amount', 'e-contract_payroll.public_holiday_ot_3_0', 'e-contract_payroll.deduction_hostel', 'e-contract_payroll.sosco_deduction', 'e-contract_payroll.sosco_contribution', 'workers.created_at')
        ->get();
    }

    /**
     * Show the worker with related visa and employment and bank details and payroll and project.
     * 
     * @param array $request The request data containing payroll id, company id
     * @return mixed Returns the worker with related visa and employment and bank details and payroll and project.
     */ 
    public function show($request): mixed
    {
        $user = JWTAuth::parseToken()->authenticate();
        return $this->workers
            ->leftJoin('worker_visa', 'worker_visa.worker_id', 'workers.id')
            ->leftJoin('worker_employment', function($query) {
                $query->on('worker_employment.worker_id','=','workers.id');
            })
            ->leftJoin('worker_bank_details', function($query) {
                $query->on('worker_bank_details.worker_id','=','workers.id')
                    ->whereRaw('worker_bank_details.id IN (select MIN(WORKER_BANK.id) from worker_bank_details as WORKER_BANK JOIN workers as WORKER ON WORKER.id = WORKER_BANK.worker_id group by WORKER.id)');
            })
            ->leftJoin('e-contract_payroll', 'e-contract_payroll.worker_id', 'worker_employment.worker_id')
            ->leftJoin('e-contract_project', 'e-contract_project.id', 'worker_employment.project_id')
            ->where('workers.company_id', $user['company_id'])  
            ->where('e-contract_payroll.id', $request['id'])       
            ->select('workers.id', 'workers.name', 'worker_bank_details.account_number', 'worker_bank_details.account_number', 'worker_bank_details.socso_number', 'workers.passport_number', 'worker_employment.department', 'e-contract_payroll.month', 'e-contract_payroll.year', 'e-contract_payroll.basic_salary', 'e-contract_payroll.ot_1_5', 'e-contract_payroll.ot_2_0', 'e-contract_payroll.ot_3_0', 'e-contract_payroll.ph', 'e-contract_payroll.rest_day', 'e-contract_payroll.deduction_advance', 'e-contract_payroll.deduction_accommodation', 'e-contract_payroll.annual_leave', 'e-contract_payroll.medical_leave', 'e-contract_payroll.hospitalisation_leave', 'e-contract_payroll.amount', 'e-contract_payroll.no_of_workingdays', 'e-contract_payroll.normalday_ot_1_5', 'e-contract_payroll.ot_1_5_hrs_amount', 'e-contract_payroll.restday_daily_salary_rate', 'e-contract_payroll.hrs_ot_2_0', 'e-contract_payroll.ot_2_0_hrs_amount', 'e-contract_payroll.public_holiday_ot_3_0', 'e-contract_payroll.deduction_hostel', 'e-contract_payroll.sosco_deduction', 'e-contract_payroll.sosco_contribution')
            ->distinct('workers.id')->get();
    }

    /**
     * Importing a new e-contract payroll from the given request data.
     * 
     * @param $request The request data containing e-contract project id and file.
     * @return mixed Returns an array with the following keys:
     * - "unauthorizedError": A array returns unauthorized if e-contract project is null.
     * - "validate": An array of validation errors, if any.
     * - "isSubmit": A boolean indicating if the e-contract payroll import was successfully created.
     */
    public function import($request, $file): mixed
    {
        $user = JWTAuth::parseToken()->authenticate();
        $request['created_by'] = $user['id'];
        $request['modified_by'] = $user['id'];
        $request['company_id'] = $user['company_id'];

        $checkProject = $this->showEContractProject($request);
        if (is_null($checkProject)) {
            return [
                'unauthorizedError' => true
            ];
        }

        $validator = Validator::make($request->toArray(), $this->importValidation());
        if ($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }
        
        $eContractPayrollBulkUpload = $this->eContractPayrollBulkUpload->create([
            'project_id' => $request['project_id'] ?? '',
            'name' => self::PAYROLL_UPLOAD_TYPE,
            'type' => self::PAYROLL_UPLOAD_TYPE,
            'company_id' => $request['company_id'],
            'created_by' => $request['created_by'],
            'modified_by' => $request['created_by']
        ]);

        $rows = Excel::toArray(new EContractPayrollImport($request, $eContractPayrollBulkUpload), $file);
        
        $this->eContractPayrollBulkUpload->where('id', $eContractPayrollBulkUpload->id)
        ->update(['actual_row_count' => count($rows[0])]);

        Excel::import(new EContractPayrollImport($request, $eContractPayrollBulkUpload), $file);

        return true;
    }

    /**
     * Creates a new e-contract payroll from the given request data.
     * 
     * @param array $request The array containing payroll data.
     *                      The array should have the following keys:
     *                      - worker_id: The worker id of the payroll.
     *                      - project_id: The project id of the payroll.
     *                      - month: The month of the payroll.
     *                      - year: The year of the payroll.
     *                      - basic_salary: The basic salary of the payroll.
     *                      - ot_1_5: The ot_1_5 of the payroll.
     *                      - ot_2_0: The ot_2_0 of the payroll.
     *                      - ot_3_0: The ot_3_0 of the payroll.
     *                      - ph: The ph of the payroll.
     *                      - rest_day: The rest day of the payroll.
     *                      - deduction_advance: The deduction advance of the payroll.
     *                      - deduction_accommodation: The deduction accommodation of the payroll.
     *                      - annual_leave: The annual leave of the payroll.
     *                      - medical_leave: The medical leave of the payroll.
     *                      - hospitalisation_leave: The hospitalisation leave of the payroll.
     *                      - amount: The amount of the payroll.
     *                      - no_of_workingdays: The no of workingdays of the payroll.
     *                      - normalday_ot_1_5: The normalday ot_1_5 of the payroll.
     *                      - ot_1_5_hrs_amount: The ot_1_5 hrs amount of the payroll.
     *                      - restday_daily_salary_rate: The restday daily salary rate of the payroll.
     *                      - hrs_ot_2_0: The hrs ot_2_0 of the payroll.
     *                      - ot_2_0_hrs_amount: The ot_2_0 hrs amount of the payroll.
     *                      - public_holiday_ot_3_0: The public holiday ot_3_0 of the payroll.
     *                      - deduction_hostel: The deduction hostel of the payroll.
     *                      - sosco_deduction: The sosco deduction of the payroll.
     *                      - sosco_contribution: The sosco contribution of the payroll.
     *                      - created_by: The ID of the user who created the payroll.
     *                      - modified_by: The updated payroll modified by.
     * 
     * @return bool|array Returns an array with the following keys:
     * - "validate": An array of validation errors, if any.
     * - "false": A boolean returns false if e-contract payroll is null.
     * - "isSubmit": A boolean indicating if the e-contract payroll was successfully created.
     */
    public function add($request): bool|array
    {
        $validator = Validator::make($request, $this->addValidation());
        if ($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }

        $eContractPayroll = $this->eContractPayroll->where([
            ['worker_id', $request['worker_id']],
            ['project_id', $request['project_id']],
            ['month', $request['month']],
            ['year', $request['year']],
        ])->first(['id', 'worker_id', 'project_id', 'month', 'year']);
        if (is_null($eContractPayroll)) {
            $this->createEContractPayroll($request);
            return true;
        } else {
            return false;
        }
    }

    /**
     * Updates the e-contract payroll from the given request data.
     * 
     * @param object $eContractPayroll The payroll object to be updated.
     * @param array $request The array containing payroll data.
     *                      The array should have the following keys:
     *                      - basic_salary: The updated basic salary.
     *                      - ot_1_5: The updated ot_1_5.
     *                      - ot_2_0: The updated ot_2_0.
     *                      - ot_3_0: The updated ot_3_0.
     *                      - ph: The updated ph.
     *                      - rest_day: The updated rest day.
     *                      - deduction_advance: The updated deduction advance.
     *                      - deduction_accommodation: The updated deduction accommodation.
     *                      - annual_leave: The updated annual leave.
     *                      - medical_leave: The updated medical leave.
     *                      - hospitalisation_leave: The updated hospitalisation leave.
     *                      - amount: The updated amount.
     *                      - no_of_workingdays: The updated no of workingdays.
     *                      - normalday_ot_1_5: The updated normalday ot_1_5.
     *                      - ot_1_5_hrs_amount: The updated ot_1_5 hrs amount.
     *                      - restday_daily_salary_rate: The updated restday daily salary rate.
     *                      - hrs_ot_2_0: The updated hrs ot_2_0.
     *                      - ot_2_0_hrs_amount: The updated ot_2_0 hrs amount.
     *                      - public_holiday_ot_3_0: The updated public holiday ot_3_0.
     *                      - deduction_hostel: The updated deduction hostel.
     *                      - sosco_deduction: The updated sosco deduction.
     *                      - sosco_contribution: The updated sosco contribution.     *                      - modified_by: The updated payroll modified by.
     * 
     * @return bool|array Returns an array with the following keys:
     * - "validate": An array of validation errors, if any.
     * - "false": A boolean returns false if e-contract payroll is null.
     * - "isSubmit": A boolean indicating if the e-contract payroll was successfully updated.
     */
    public function update($request): bool|array
    {
        $validator = Validator::make($request, $this->updateValidation());
        if ($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }

        $user = JWTAuth::parseToken()->authenticate();
        $eContractPayroll = $this->eContractPayroll
        ->join('workers', function($query) use($user) {
            $query->on('workers.id','=','e-contract_payroll.worker_id')
            ->where('workers.company_id', $user['company_id']);
        })
        ->select('e-contract_payroll.id', 'e-contract_payroll.worker_id', 'e-contract_payroll.project_id', 'e-contract_payroll.month', 'e-contract_payroll.year', 'e-contract_payroll.basic_salary', 'e-contract_payroll.ot_1_5', 'e-contract_payroll.ot_2_0', 'e-contract_payroll.ot_3_0', 'e-contract_payroll.ph', 'e-contract_payroll.rest_day', 'e-contract_payroll.deduction_advance', 'e-contract_payroll.deduction_accommodation', 'e-contract_payroll.annual_leave', 'e-contract_payroll.medical_leave', 'e-contract_payroll.hospitalisation_leave', 'e-contract_payroll.amount', 'e-contract_payroll.no_of_workingdays', 'e-contract_payroll.normalday_ot_1_5', 'e-contract_payroll.ot_1_5_hrs_amount', 'e-contract_payroll.restday_daily_salary_rate', 'e-contract_payroll.hrs_ot_2_0', 'e-contract_payroll.ot_2_0_hrs_amount', 'e-contract_payroll.public_holiday_ot_3_0', 'e-contract_payroll.deduction_hostel', 'e-contract_payroll.created_by', 'e-contract_payroll.modified_by', 'e-contract_payroll.sosco_deduction', 'e-contract_payroll.sosco_contribution', 'e-contract_payroll.created_at', 'e-contract_payroll.updated_at', 'e-contract_payroll.deleted_at')
        ->find($request['id']);
        if (is_null($eContractPayroll)) {
            return [
                'unauthorizedError' => true
            ];
        }

        $this->updateEContractPayroll($eContractPayroll, $request);

        return true;
    }

    /**
     * Returns a paginated list of timesheet based on the given project id.
     * 
     * @param array $request The array containing project id.
     * @return mixed Returns the paginated list of timesheet with related payroll and attachments.
     */ 
    public function listTimesheet($request): mixed
    {
        $user = JWTAuth::parseToken()->authenticate();
        return $this->eContractPayrollAttachments
            ->join('e-contract_payroll', 'e-contract_payroll.id', 'e-contract_payroll_attachments.file_id')
            ->join('workers', function($query) use($user) {
                $query->on('workers.id','=','e-contract_payroll.worker_id')
                ->where('workers.company_id', $user['company_id']);
            })
            ->where('e-contract_payroll_attachments.file_id', $request['project_id'])
            ->select('e-contract_payroll_attachments.id', 'e-contract_payroll_attachments.month', 'e-contract_payroll_attachments.year', 'e-contract_payroll_attachments.file_id', 'e-contract_payroll_attachments.file_name', 'e-contract_payroll_attachments.file_type', 'e-contract_payroll_attachments.file_url', 'e-contract_payroll_attachments.created_at')
            ->distinct('e-contract_payroll_attachments.id')
            ->orderBy('e-contract_payroll_attachments.id','DESC')
            ->paginate(Config::get('services.paginate_row'));
    }

    /**
     * Show the timesheet with related attachment and payroll.
     * 
     * @param array $request The request data containing attachment project id, month, year
     * @return mixed Returns the timesheet with related attachment and payroll.
     */ 
    public function viewTimesheet($request): mixed
    {
        $user = JWTAuth::parseToken()->authenticate();
        return $this->eContractPayrollAttachments
        ->join('e-contract_payroll', 'e-contract_payroll.id', 'e-contract_payroll_attachments.file_id')
        ->join('workers', function($query) use($user) {
            $query->on('workers.id','=','e-contract_payroll.worker_id')
            ->where('workers.company_id', $user['company_id']);
        })
        ->where([
            'e-contract_payroll_attachments.file_id' => $request['project_id'],
            'e-contract_payroll_attachments.month' => $request['month'],
            'e-contract_payroll_attachments.year' => $request['year']
        ])
        ->select('e-contract_payroll_attachments.id', 'e-contract_payroll_attachments.month', 'e-contract_payroll_attachments.year', 'e-contract_payroll_attachments.file_id', 'e-contract_payroll_attachments.file_name', 'e-contract_payroll_attachments.file_type', 'e-contract_payroll_attachments.file_url', 'e-contract_payroll_attachments.created_at')
        ->get();
    }

    /**
     * Upload a new timesheet process from the given request data.
     * 
     * @param array $request The array containing expenses data.
     *                      The array should have the following keys:
     *                      - project_id: The project id of the timesheet.
     *                      - month: The month of the timesheet.
     *                      - year: The year of the timesheet.
     * @return bool|array Returns an array with the following keys:
     * - "unauthorizedError": A array returns unauthorized if project is null.
     * - "validate": An array of validation errors, if any.
     * - "isSubmit": A boolean indicating if the timesheet was successfully upload.
     */
    public function uploadTimesheet($request): bool|array
    {
        $user = JWTAuth::parseToken()->authenticate();
        $request['created_by'] = $user['id'];
        $request['company_id'] = $user['company_id'];

        $checkProject = $this->showEContractProject($request);
        if (is_null($checkProject)) {
            return [
                'unauthorizedError' => true
            ];
        }

        $validator = Validator::make($request->toArray(), $this->uploadTimesheetValidation());
        if ($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }

        $eContractPayrollAttachments = $this->eContractPayrollAttachments
        ->join('e-contract_payroll', 'e-contract_payroll.id', 'e-contract_payroll_attachments.file_id')
        ->join('workers', function($query) use($user) {
            $query->on('workers.id','=','e-contract_payroll.worker_id')
            ->where('workers.company_id', $user['company_id']);
        })
        ->where([
            'e-contract_payroll_attachments.file_id' => $request['project_id'],
            'e-contract_payroll_attachments.month' => $request['month'],
            'e-contract_payroll_attachments.year' => $request['year']
        ])->first(['e-contract_payroll_attachments.id', 'e-contract_payroll_attachments.month', 'e-contract_payroll_attachments.year', 'e-contract_payroll_attachments.file_id', 'e-contract_payroll_attachments.file_name', 'e-contract_payroll_attachments.file_type', 'e-contract_payroll_attachments.file_url', 'e-contract_payroll_attachments.created_at']);

        $this->uploadEContractPayrollAttachments($eContractPayrollAttachments, $request);
        
        return false;
    }

    /**
     * Creates a new e-contract cost management from the given request data.
     * 
     * @param array $request The array containing e-contract cost data.
     *                      The array should have the following keys:
     *                      - project_id: The project id of the cost.
     *                      - title: The title of the cost.
     *                      - type: The type of the cost.
     *                      - payment_reference_number: The payment reference number of the cost.
     *                      - payment_date: The payment date of the cost.
     *                      - is_payroll: The payroll of the cost.
     *                      - quantity: The quantity of the cost.
     *                      - amount: The amount of the cost.
     *                      - remarks: The remarks of the cost.
     *                      - payroll_id: The payroll id of the cost.
     *                      - month: The month of the cost.
     *                      - year: The year of the cost.
     *                      - created_by: The ID of the user who created the cost.
     *                      - modified_by: The updated cost modified by.
     * 
     * @return bool|array Returns an array with the following keys:
     * - "existsError": A array returns project exist if e-contract cost management is null.
     * - "noRecords": A array returns noRecords if payroll workers is null.
     * - "isSubmit": A boolean indicating if the authorize payroll was successfully created.
     */
    public function authorizePayroll($request): bool|array
    {
        $user = JWTAuth::parseToken()->authenticate();
        $request['worker_id'] = $user['worker_id'];

        $checkEContractCostManagement = $this->eContractCostManagement->where('project_id',$request['project_id'])->where('month',$request['month'])->where('year',$request['year'])->count();
        if ($checkEContractCostManagement > self::ECONTRACT_COST_MANAGEMENT_0) {
            return [
                'existsError' => true
            ];
        }

        $payrollWorkers = $this->workers
        ->leftJoin('worker_employment', function($query) {
            $query->on('worker_employment.worker_id','=','workers.id');
        })
        ->leftJoin('e-contract_payroll', function($query) use ($request) {
            $query->on('e-contract_payroll.worker_id','=','workers.id');
            if (isset($request['project_id']) && !empty($request['project_id']) && empty($request['month']) && empty($request['year'])) {
            $query->whereRaw('e-contract_payroll.id IN (select MAX(TMPAY.id) from e-contract_payroll as TMPAY JOIN workers as WORKER ON WORKER.id = TMPAY.worker_id group by WORKER.id)');
            }
        })
        ->leftJoin('e-contract_project', function($query) {
            $query->on('e-contract_payroll.project_id','=','e-contract_project.id');
        })
        ->where(function ($query) use ($request) {
            if (isset($request['month']) && !empty($request['month'])) {
                $query->where('e-contract_payroll.month', $request['month']);
            }
            if (isset($request['year']) && !empty($request['year'])) {
                $query->where('e-contract_payroll.year', $request['year']);
            }
            if (isset($request['project_id']) && !empty($request['project_id']) && empty($request['month']) && empty($request['year'])) {
                $query->whereNull('worker_employment.work_end_date');
                $query->whereNull('worker_employment.remove_date');
            }
        })
        ->where('workers.company_id', $user['company_id'])
        ->select('workers.id as worker_id', 'workers.name', 'e-contract_payroll.id as payroll_id', 'e-contract_payroll.amount', 'e-contract_payroll.sosco_contribution', 'e-contract_project.application_id', 'workers.created_at')
        ->distinct('workers.id')
        ->orderBy('workers.created_at','DESC')->get();

        if (isset($payrollWorkers) && count($payrollWorkers) > self::PAYROLL_WORKERS_0) {
            $this->createEContractCostManagement($payrollWorkers, $request);
        } else {
            return [
                'noRecords' => true
            ];
        }

        return true;
    }

    /**
     * Returns a paginated list of e-contract payroll bulk upload based on the given search request.
     * 
     * @param array $request The search request parameters.
     * @return mixed Returns a paginated list of e-contract payroll bulk upload.
     */
    public function importHistory($request): mixed
    {
        $user = JWTAuth::parseToken()->authenticate();
        $request['company_id'] = $this->authServices->getCompanyIds($user);

        return $this->eContractPayrollBulkUpload
        ->select('id', 'actual_row_count', 'total_success', 'total_failure', 'process_status', 'created_at')
        ->where('process_status', self::PROCESS_STATUS_PROCESSED)
        ->whereNotNull('failure_case_url')
        ->whereIn('company_id', $request['company_id'])
        ->orderBy('id', 'desc')
        ->paginate(Config::get('services.paginate_row'));
    }

    /**
     * e-Contract payroll import failure excel download
     * 
     * @param array $request The request data containing company id, bulk upload id
     * @return array Returns an array with the following keys:
     * - "InvalidUser": A array returns Invalid if check e-contract payroll bulk upload is null.
     * - "queueError": A array returns queueError if check process status is null.
     */
    public function failureExport($request): array
    {        
        $payrollBulkUpload = $this->eContractPayrollBulkUpload
                        ->where('company_id', $request['company_id'])
                        ->where('id', $request['bulk_upload_id'])
                        ->first();
        if(is_null($payrollBulkUpload)) {
            return [
                'InvalidUser' => true
            ];
        }

        if($payrollBulkUpload->process_status != self::PROCESS_STATUS_PROCESSED || is_null($payrollBulkUpload->failure_case_url)) {
            return [
                'queueError' => true
            ];
        }
        return [
            'file_url' => $payrollBulkUpload->failure_case_url
        ];
    }

    /**
     * e-Contract payroll import failure case excel file creation
     * 
     * @return bool indicating if the prepare excel for failureCases.
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
     * Show the e-contract payroll bulk upload.
     * 
     * @return mixed Returns the e-contract payroll bulk upload.
     */
    private function getPayrollBulkUploadRows(): mixed
    {
        return $this->eContractPayrollBulkUpload
        ->where( function ($query) {
            $query->whereNull('process_status')
            ->orWhereNull('failure_case_url');
        })
        ->select('id', 'total_records', 'total_success', 'total_failure', 'actual_row_count')
        ->get()->toArray();
    }

    /**
     * Updates the payroll status from the given request data.
     * 
     * @param array $request The array containing array of payroll id and status.
     */
    private function updatePayrollBulkUploadStatus($ids)
    {
        $this->eContractPayrollBulkUpload->whereIn('id', $ids)->update(['process_status' => self::PROCESS_STATUS_PROCESSED]);
    }

    /**
     * Creates a new payroll failure cases document.
     * 
     * @param array $ids The id data containing failure of payroll.
     */
    private function createPayrollFailureCasesDocument($ids): void
    {
        foreach($ids as $id) {
            $fileName = "FailureCases" . $id . ".xlsx";
            $filePath = '/FailureCases/eContract/' . $fileName; 
            Excel::store(new EContractPayrollFailureExport($id), $filePath, 'linode');
            $fileUrl = $this->storage::disk('linode')->url($filePath);
            $this->eContractPayrollBulkUpload->where('id', $id)->update(['failure_case_url' => $fileUrl]);
        }
    }
    
    /**
     * Returns a paginated list of workers based on the given search request.
     * 
     * @param array $request The search request parameters.
     * @return mixed Returns a paginated list of workers with related visa and employment and bank details and payroll and project.
     */
    public function getWorkersQuery($request): mixed
    {
        return $this->workers
            ->leftJoin('worker_visa', 'worker_visa.worker_id', 'workers.id')
            ->leftJoin('worker_employment', function($query) {
                $query->on('worker_employment.worker_id','=','workers.id');
            })
            ->leftJoin('worker_bank_details', function($query) {
                $query->on('worker_bank_details.worker_id','=','workers.id')
                    ->whereRaw('worker_bank_details.id IN (select MIN(WORKER_BANK.id) from worker_bank_details as WORKER_BANK JOIN workers as WORKER ON WORKER.id = WORKER_BANK.worker_id group by WORKER.id)');
            })
            ->leftJoin('e-contract_payroll', function($query) use ($request) {
                $query->on('e-contract_payroll.worker_id','=','worker_employment.worker_id');
                if (isset($request['project_id']) && !empty($request['project_id']) && empty($request['month']) && empty($request['year'])) {
                $query->whereRaw("`e-contract_payroll`.`id` IN (select MAX(PAY.id) from `e-contract_payroll` as PAY JOIN workers as WORKER ON WORKER.id = PAY.worker_id group by WORKER.id)");
                }
            })
            ->leftJoin('e-contract_project', 'e-contract_project.id', 'worker_employment.project_id')
            ->where('worker_employment.project_id', $request['project_id'])
            ->where('worker_employment.service_type', self::SERVICE_TYPE_ECONTRACT)
            ->where('workers.company_id', $request['company_id'])
            ->where(function ($query) use ($request) {
                if (isset($request['search']) && !empty($request['search'])) {
                    $query->where('workers.name', 'like', "%{$request['search']}%")
                    ->orWhere('workers.passport_number', 'like', '%'.$request['search'].'%')
                    ->orWhere('worker_employment.department', 'like', '%'.$request['search'].'%');
                }
                if (isset($request['month']) && !empty($request['month'])) {
                    $query->where('e-contract_payroll.month', $request['month']);
                }
                if (isset($request['year']) && !empty($request['year'])) {
                    $query->where('e-contract_payroll.year', $request['year']);
                }
                if (isset($request['project_id']) && !empty($request['project_id']) && empty($request['month']) && empty($request['year'])) {
                    $query->whereNull('worker_employment.work_end_date');
                    $query->whereNull('worker_employment.remove_date');
                    $query->whereIn('workers.econtract_status', Config::get('services.ECONTRACT_WORKER_STATUS'));
                }
            })
            ->distinct('workers.id')
            ->orderBy('workers.created_at','DESC');
    }
    
    /**
     * Show the e-contract project with related applications.
     * 
     * @param array $request The request data containing project id, company id
     * @return mixed Returns the e-contract project with related applications.
     */
    public function showEContractProject($request): mixed
    {
        return $this->eContractProject
        ->join('e-contract_applications', function($query) use($request) {
            $query->on('e-contract_applications.id','=','e-contract_project.application_id')
            ->where('e-contract_applications.company_id', $request['company_id']);
        })
        ->select('e-contract_project.id', 'e-contract_project.application_id', 'e-contract_project.name', 'e-contract_project.state', 'e-contract_project.city', 'e-contract_project.address', 'e-contract_project.annual_leave', 'e-contract_project.medical_leave', 'e-contract_project.hospitalization_leave', 'e-contract_project.created_by', 'e-contract_project.modified_by', 'e-contract_project.valid_until', 'e-contract_project.created_at', 'e-contract_project.updated_at', 'e-contract_project.deleted_at')
        ->find($request['project_id']);
    }
    
    /**
     * Creates a new e-contract payroll from the given request data.
     * 
     * @param array $request The array containing payroll data.
     *                      The array should have the following keys:
     *                      - worker_id: The worker id of the payroll.
     *                      - project_id: The project id of the payroll.
     *                      - month: The month of the payroll.
     *                      - year: The year of the payroll.
     *                      - basic_salary: The basic salary of the payroll.
     *                      - ot_1_5: The ot_1_5 of the payroll.
     *                      - ot_2_0: The ot_2_0 of the payroll.
     *                      - ot_3_0: The ot_3_0 of the payroll.
     *                      - ph: The ph of the payroll.
     *                      - rest_day: The rest day of the payroll.
     *                      - deduction_advance: The deduction advance of the payroll.
     *                      - deduction_accommodation: The deduction accommodation of the payroll.
     *                      - annual_leave: The annual leave of the payroll.
     *                      - medical_leave: The medical leave of the payroll.
     *                      - hospitalisation_leave: The hospitalisation leave of the payroll.
     *                      - amount: The amount of the payroll.
     *                      - no_of_workingdays: The no of workingdays of the payroll.
     *                      - normalday_ot_1_5: The normalday ot_1_5 of the payroll.
     *                      - ot_1_5_hrs_amount: The ot_1_5 hrs amount of the payroll.
     *                      - restday_daily_salary_rate: The restday daily salary rate of the payroll.
     *                      - hrs_ot_2_0: The hrs ot_2_0 of the payroll.
     *                      - ot_2_0_hrs_amount: The ot_2_0 hrs amount of the payroll.
     *                      - public_holiday_ot_3_0: The public holiday ot_3_0 of the payroll.
     *                      - deduction_hostel: The deduction hostel of the payroll.
     *                      - sosco_deduction: The sosco deduction of the payroll.
     *                      - sosco_contribution: The sosco contribution of the payroll.
     *                      - created_by: The ID of the user who created the payroll.
     *                      - modified_by: The updated payroll modified by.
     */
    public function createEContractPayroll($request): void
    {
        $this->eContractPayroll->create([
            'worker_id' => $request['worker_id'] ?? self::DEFAULT_INTEGER_VALUE_ZERO,
            'project_id' => $request['project_id'] ?? self::DEFAULT_INTEGER_VALUE_ZERO,
            'month' => $request['month'] ?? self::DEFAULT_INTEGER_VALUE_ZERO,
            'year' => $request['year'] ?? self::DEFAULT_INTEGER_VALUE_ZERO,
            'basic_salary' => $request['basic_salary'] ?? self::DEFAULT_INTEGER_VALUE_ZERO,
            'ot_1_5' => $request['ot_1_5'] ?? self::DEFAULT_INTEGER_VALUE_ZERO,
            'ot_2_0' => $request['ot_2_0'] ?? self::DEFAULT_INTEGER_VALUE_ZERO,
            'ot_3_0' => $request['ot_3_0'] ?? self::DEFAULT_INTEGER_VALUE_ZERO,
            'ph' => $request['ph'] ?? self::DEFAULT_INTEGER_VALUE_ZERO,
            'rest_day' => $request['rest_day'] ?? self::DEFAULT_INTEGER_VALUE_ZERO,
            'deduction_advance' => $request['deduction_advance'] ?? self::DEFAULT_INTEGER_VALUE_ZERO,
            'deduction_accommodation' => $request['deduction_accommodation'] ?? self::DEFAULT_INTEGER_VALUE_ZERO,
            'annual_leave' => $request['annual_leave'] ?? self::DEFAULT_INTEGER_VALUE_ZERO,
            'medical_leave' => $request['medical_leave'] ?? self::DEFAULT_INTEGER_VALUE_ZERO,
            'hospitalisation_leave' => $request['hospitalisation_leave'] ?? self::DEFAULT_INTEGER_VALUE_ZERO,
            'amount' => $request['amount'] ?? self::DEFAULT_INTEGER_VALUE_ZERO,
            'no_of_workingdays' => $request['no_of_workingdays'] ?? self::DEFAULT_INTEGER_VALUE_ZERO,
            'normalday_ot_1_5' => $request['normalday_ot_1_5'] ?? self::DEFAULT_INTEGER_VALUE_ZERO,
            'ot_1_5_hrs_amount' => $request['ot_1_5_hrs_amount'] ?? self::DEFAULT_INTEGER_VALUE_ZERO,
            'restday_daily_salary_rate' => $request['restday_daily_salary_rate'] ?? self::DEFAULT_INTEGER_VALUE_ZERO,
            'hrs_ot_2_0' => $request['hrs_ot_2_0'] ?? self::DEFAULT_INTEGER_VALUE_ZERO,
            'ot_2_0_hrs_amount' => $request['ot_2_0_hrs_amount'] ?? self::DEFAULT_INTEGER_VALUE_ZERO,
            'public_holiday_ot_3_0' => $request['public_holiday_ot_3_0'] ?? self::DEFAULT_INTEGER_VALUE_ZERO,
            'deduction_hostel' => $request['deduction_hostel'] ?? self::DEFAULT_INTEGER_VALUE_ZERO,
            'sosco_deduction' => $request['sosco_deduction'] ?? self::DEFAULT_INTEGER_VALUE_ZERO,
            'sosco_contribution' => $request['sosco_contribution'] ?? self::DEFAULT_INTEGER_VALUE_ZERO,
            'created_by' => $request['created_by'] ?? self::DEFAULT_INTEGER_VALUE_ZERO,
            'modified_by' => $request['created_by'] ?? self::DEFAULT_INTEGER_VALUE_ZERO
        ]);
    }

    /**
     * Updates the e-contract payroll from the given request data.
     * 
     * @param object $eContractPayroll The payroll object to be updated.
     * @param array $request The array containing payroll data.
     *                      The array should have the following keys:
     *                      - basic_salary: The updated basic salary.
     *                      - ot_1_5: The updated ot_1_5.
     *                      - ot_2_0: The updated ot_2_0.
     *                      - ot_3_0: The updated ot_3_0.
     *                      - ph: The updated ph.
     *                      - rest_day: The updated rest day.
     *                      - deduction_advance: The updated deduction advance.
     *                      - deduction_accommodation: The updated deduction accommodation.
     *                      - annual_leave: The updated annual leave.
     *                      - medical_leave: The updated medical leave.
     *                      - hospitalisation_leave: The updated hospitalisation leave.
     *                      - amount: The updated amount.
     *                      - no_of_workingdays: The updated no of workingdays.
     *                      - normalday_ot_1_5: The updated normalday ot_1_5.
     *                      - ot_1_5_hrs_amount: The updated ot_1_5 hrs amount.
     *                      - restday_daily_salary_rate: The updated restday daily salary rate.
     *                      - hrs_ot_2_0: The updated hrs ot_2_0.
     *                      - ot_2_0_hrs_amount: The updated ot_2_0 hrs amount.
     *                      - public_holiday_ot_3_0: The updated public holiday ot_3_0.
     *                      - deduction_hostel: The updated deduction hostel.
     *                      - sosco_deduction: The updated sosco deduction.
     *                      - sosco_contribution: The updated sosco contribution.     *                      - modified_by: The updated payroll modified by.
     */
    public function updateEContractPayroll($eContractPayroll, $request): void
    {
        $eContractPayroll->basic_salary =  $request['basic_salary'] ?? $eContractPayroll->basic_salary;
        $eContractPayroll->ot_1_5 =  $request['ot_1_5'] ?? $eContractPayroll->ot_1_5;
        $eContractPayroll->ot_2_0 =  $request['ot_2_0'] ?? $eContractPayroll->ot_2_0;
        $eContractPayroll->ot_3_0 =  $request['ot_3_0'] ?? $eContractPayroll->ot_3_0;
        $eContractPayroll->ph =  $request['ph'] ?? $eContractPayroll->ph;
        $eContractPayroll->rest_day =  $request['rest_day'] ?? $eContractPayroll->rest_day;
        $eContractPayroll->deduction_advance =  $request['deduction_advance'] ?? $eContractPayroll->deduction_advance;
        $eContractPayroll->deduction_accommodation =  $request['deduction_accommodation'] ?? $eContractPayroll->deduction_accommodation;
        $eContractPayroll->annual_leave =  $request['annual_leave'] ?? $eContractPayroll->annual_leave;
        $eContractPayroll->medical_leave =  $request['medical_leave'] ?? $eContractPayroll->medical_leave;
        $eContractPayroll->hospitalisation_leave =  $request['hospitalisation_leave'] ?? $eContractPayroll->hospitalisation_leave;
        $eContractPayroll->amount =  $request['amount'] ?? $eContractPayroll->amount;
        $eContractPayroll->no_of_workingdays =  $request['no_of_workingdays'] ?? $eContractPayroll->no_of_workingdays;
        $eContractPayroll->normalday_ot_1_5 =  $request['normalday_ot_1_5'] ?? $eContractPayroll->normalday_ot_1_5;
        $eContractPayroll->ot_1_5_hrs_amount =  $request['ot_1_5_hrs_amount'] ?? $eContractPayroll->ot_1_5_hrs_amount;
        $eContractPayroll->restday_daily_salary_rate =  $request['restday_daily_salary_rate'] ?? $eContractPayroll->restday_daily_salary_rate;
        $eContractPayroll->hrs_ot_2_0 =  $request['hrs_ot_2_0'] ?? $eContractPayroll->hrs_ot_2_0;
        $eContractPayroll->ot_2_0_hrs_amount =  $request['ot_2_0_hrs_amount'] ?? $eContractPayroll->ot_2_0_hrs_amount;
        $eContractPayroll->public_holiday_ot_3_0 =  $request['public_holiday_ot_3_0'] ?? $eContractPayroll->public_holiday_ot_3_0;
        $eContractPayroll->deduction_hostel =  $request['deduction_hostel'] ?? $eContractPayroll->deduction_hostel;
        $eContractPayroll->sosco_deduction =  $request['sosco_deduction'] ?? $eContractPayroll->sosco_deduction;
        $eContractPayroll->sosco_contribution =  $request['sosco_contribution'] ?? $eContractPayroll->sosco_contribution;
        $eContractPayroll->modified_by =  $request['modified_by'] ?? $eContractPayroll->modified_by;
        $eContractPayroll->save();
    }
    
    /**
     * Creates a new e-contract cost management from the given request data.
     * 
     * @param array $request The array containing e-contract cost data.
     *                      The array should have the following keys:
     *                      - project_id: The project id of the cost.
     *                      - title: The title of the cost.
     *                      - type: The type of the cost.
     *                      - payment_reference_number: The payment reference number of the cost.
     *                      - payment_date: The payment date of the cost.
     *                      - is_payroll: The payroll of the cost.
     *                      - quantity: The quantity of the cost.
     *                      - amount: The amount of the cost.
     *                      - remarks: The remarks of the cost.
     *                      - payroll_id: The payroll id of the cost.
     *                      - month: The month of the cost.
     *                      - year: The year of the cost.
     *                      - created_by: The ID of the user who created the cost.
     *                      - modified_by: The updated cost modified by.
     */
    public function createEContractCostManagement($payrollWorkers, $request): void
    {
        foreach($payrollWorkers as $result){
            $this->eContractCostManagement->create([
                'project_id' => $request['project_id'],
                'title' => $result['name'],
                'type' => self::PAYROLL_TYPE,
                'payment_reference_number' => self::DEFAULT_INTEGER_VALUE_ONE,
                'payment_date' => Carbon::now(),
                'is_payroll' => self::DEFAULT_INTEGER_VALUE_ONE,
                'quantity' => self::DEFAULT_INTEGER_VALUE_ONE,
                'amount' => $result['amount'],
                'remarks' => $result['name'],
                'is_payroll' => self::DEFAULT_INTEGER_VALUE_ONE,
                'payroll_id' => $result['payroll_id'],
                'month' => $request['month'],
                'year' => $request['year'],
                'created_by'    => $request['worker_id'] ?? self::DEFAULT_INTEGER_VALUE_ZERO,
                'modified_by'   => $request['worker_id'] ?? self::DEFAULT_INTEGER_VALUE_ZERO,
            ]);

            $this->eContractCostManagement->create([
                'project_id' => $request['project_id'] ?? self::DEFAULT_INTEGER_VALUE_ZERO,
                'title' => self::SOCSO_CONTRIBUTION . " (" . $result['name'] . " )",
                'type' => self::PAYROLL_TYPE,
                'payment_reference_number' => self::DEFAULT_INTEGER_VALUE_ONE,
                'payment_date' => Carbon::now(),
                'is_payroll' => self::DEFAULT_INTEGER_VALUE_ONE,
                'quantity' => self::DEFAULT_INTEGER_VALUE_ONE,
                'amount' => $result['amount'],
                'remarks' => self::SOCSO_CONTRIBUTION . " (" . $result['name'] . " )",
                'is_payroll' => self::DEFAULT_INTEGER_VALUE_ONE,
                'payroll_id' => $result['payroll_id'],
                'month' => $request['month'],
                'year' => $request['year'],
                'created_by'    => $request['worker_id'] ?? self::DEFAULT_INTEGER_VALUE_ZERO,
                'modified_by'   => $request['worker_id'] ?? self::DEFAULT_INTEGER_VALUE_ZERO,
            ]);
        }
    }
    
    /**
     * Upload attachment of e-contract payroll attachments.
     *
     * @param object $eContractPayrollAttachments The payroll object to be updated.
     * @param array $request The request data containing e-contract payroll attachments.
     * @return - "existsError": A array returns exists if e-contract payroll attachments is null.
     */
    public function uploadEContractPayrollAttachments($eContractPayrollAttachments, $request)
    {
        if (request()->hasFile('attachment')) {
            foreach($request->file('attachment') as $file){
                
                if (is_null($eContractPayrollAttachments)) {

                    $fileName = $file->getClientOriginalName();
                    $filePath = '/eContract/payroll/timesheet/' . $fileName; 
                    $linode = $this->storage::disk('linode');
                    $linode->put($filePath, file_get_contents($file));
                    $fileUrl = $this->storage::disk('linode')->url($filePath);

                    $this->eContractPayrollAttachments::create([
                        'file_id' => $request['project_id'],
                        "month" => $request['month'] ?? self::DEFAULT_INTEGER_VALUE_ZERO,
                        "year" => $request['year'] ?? self::DEFAULT_INTEGER_VALUE_ZERO,
                        "file_name" => $fileName,
                        "file_type" => self::FILE_TYPE_TIMESHEET,
                        "file_url" =>  $fileUrl,
                        "created_by" =>  $request['created_by'] ?? self::DEFAULT_INTEGER_VALUE_ZERO,
                        "modified_by" =>  $request['created_by'] ?? self::DEFAULT_INTEGER_VALUE_ZERO
                    ]);

                    return true;
                    
                }else{
                    return [
                        'existsError' => true
                    ];
                }
            }
        }
    }
}