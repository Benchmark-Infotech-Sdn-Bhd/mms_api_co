<?php

namespace App\Services;

use App\Models\Levy;
use App\Models\DirectrecruitmentApplications;
use App\Models\FWCMS;
use App\Models\ApplicationInterviews;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Config;

class LevyServices
{

    public const ERROR_INVALID_USER = ['InvalidUser' => true];
    public const ERROR_QUOTA = ['quotaError' => true];
    public const ITEM_NAME = 'Levy Details';
    public const PAID_STATUS = 'Paid';
    public const COMPLETED_STATUS = 'Completed';
    public const REJECTED_STATUS = 'Rejected';

    /**
     * @var Levy
     */
    private Levy $levy;
    /**
     * @var DirectrecruitmentApplications
     */
    private DirectrecruitmentApplications $directrecruitmentApplications;
    /**
     * @var fwcms
     */
    private FWCMS $fwcms;
    /**
     * @var ApplicationSummaryServices
     */
    private ApplicationSummaryServices $applicationSummaryServices;
    /**
     * @var ApplicationInterviews
     */
    private ApplicationInterviews $applicationInterviews;
    /**
     * @var DirectRecruitmentExpensesServices
     */
    private DirectRecruitmentExpensesServices $directRecruitmentExpensesServices;

    /**
     * LevyServices Constructor
     * 
     * @param Levy $levy Instance of the Levy class
     * @param DirectrecruitmentApplications $directrecruitmentApplications Instance of the DirectrecruitmentApplications class 
     * @param FWCMS $fwcms; Instance of the FWCMS class
     * @param ApplicationSummaryServices $applicationSummaryServices Instance of the ApplicationSummaryServices class
     * @param ApplicationInterviews $applicationInterviews Instance of the ApplicationInterviews class
     * @param DirectRecruitmentExpensesServices $directRecruitmentExpensesServices Instance of the DirectRecruitmentExpensesServices class
     * 
     * @return void
     */
    public function __construct(
        Levy                                $levy, 
        DirectrecruitmentApplications       $directrecruitmentApplications, 
        FWCMS                               $fwcms, 
        ApplicationSummaryServices          $applicationSummaryServices, 
        ApplicationInterviews               $applicationInterviews,
        DirectRecruitmentExpensesServices   $directRecruitmentExpensesServices
    )
    {
        $this->levy = $levy;
        $this->directrecruitmentApplications = $directrecruitmentApplications;
        $this->fwcms = $fwcms;
        $this->applicationSummaryServices = $applicationSummaryServices;
        $this->applicationInterviews = $applicationInterviews;
        $this->directRecruitmentExpensesServices = $directRecruitmentExpensesServices;
    }

    /**
     * validate the create request data
     * 
     * @return array The validation rules for the input data.
     */
    public function createValidation(): array
    {
        return[
            'application_id' => 'required',
            'payment_date' => 'required|date|date_format:Y-m-d|before:tomorrow',
            'payment_amount' => 'required|regex:/^(([0-9]{0,6}+)(\.([0-9]{0,2}+))?)$/',
            'approved_quota' => 'required|regex:/^[0-9]+$/|max:3',
            'ksm_reference_number' => 'required|unique:levy',
            'payment_reference_number' => 'required|regex:/^[a-zA-Z0-9]*$/',
            'approval_number' => 'required|regex:/^[a-zA-Z0-9]*$/',
            'new_ksm_reference_number' => 'required|regex:/^[a-zA-Z0-9\/]*$/|max:21||unique:levy|different:ksm_reference_number'
        ];
    }
    
    /**
     * validate the create request data
     * 
     * @param $param
     * 
     * @return array The validation rules for the input data.
     */
    public function updateValidation($param): array
    {
        return [
            'id' => 'required',
            'application_id' => 'required',
            'payment_date' => 'required|date|date_format:Y-m-d|before:tomorrow',
            'payment_amount' => 'required|regex:/^(([0-9]{0,6}+)(\.([0-9]{0,2}+))?)$/',
            'approved_quota' => 'required|regex:/^[0-9]+$/|max:3',
            'ksm_reference_number' => 'required|unique:levy,ksm_reference_number,'.$param['id'],
            'payment_reference_number' => 'required|regex:/^[a-zA-Z0-9]*$/',
            'approval_number' => 'required|regex:/^[a-zA-Z0-9]*$/',
            'new_ksm_reference_number' => 'required|regex:/^[a-zA-Z0-9\/]*$/|max:21|different:ksm_reference_number|unique:levy,new_ksm_reference_number,'.$param['id'],
        ];
    }

    /**
     * Retrieve the application record based on requested data.
     *
     * 
     * @param array $request
     *              application_id (int) ID of the application
     * 
     * @return mixed Returns the application data

     */
    private function getApplication($request)
    {
        return $this->directrecruitmentApplications->find($request['application_id']);
    }

    /**
     * Retrieve the sum of application interview approved quota based on requested data.
     *
     * 
     * @param array $request
     *              ksm_reference_number (int) ksm reference number
     * 
     * @return int Returns the sum of application interview approved quota

     */
    private function getApplicationInterviewApprovedQuota($request)
    {
        return $this->applicationInterviews->where('ksm_reference_number', $request['ksm_reference_number'])->sum('approved_quota');
    }

    /**
     * Retrieve the count of ksm based on requested data.
     *
     * 
     * @param array $request
     *              application_id (int) ID of the application
     * 
     * @return int Returns the count of ksm reference number

     */
    private function getKsmCount($request)
    {
        return $this->fwcms->where('application_id', $request['application_id'])->count('ksm_reference_number');
    }

    /**
     * Retrieve the count of ksm based on requested data.
     *
     * 
     * @param array $request
     *              application_id (int) ID of the application
     * 
     * @return int Returns the count of ksm reference number

     */
    private function getKsmCountByStatus($request)
    {
        return $this->fwcms->where('application_id', $request['application_id'])
            ->where('status', '!=', self::REJECTED_STATUS)
            ->count('ksm_reference_number');
    }

    /**
     * Retrieve the count of levy paid on requested data.
     *
     * 
     * @param array $request
     *              application_id (int) ID of the application
     * 
     * @return int Returns the count of levy paid

     */
    private function levyPaidCount($request)
    {
        return $this->levy->where('application_id', $request['application_id'])
            ->where('status', self::PAID_STATUS)
            ->count();
    }

    /**
     * Validate the given request data.
     *
     * @param array $request The request data to be validated.
     * @return array|bool Returns an array with 'error' as key and validation error messages as value if validation fails.
     *                   Returns true if validation passes.
     */
    private function validateCreateRequest($request): array|bool
    {
        $validator = Validator::make($request, $this->createValidation());
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
    private function validateUpdateRequest($request): array|bool
    {
        $validator = Validator::make($request, $this->updateValidation($request));
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }

        return true;
    }

    /**
     * create levy
     *
     * @param array $request The request data containing the application_id, payment_date, payment_amount, approved_quota, status,  ksm_reference_number, payment_reference_number, approval_number, new_ksm_reference_number, remarks, created_by key.
     * 
     * @return void
     */
    private function createLevy($request)
    {
        $this->levy->create([
            'application_id' => $request['application_id'] ?? 0,
            'item' => $request['item'] ?? self::ITEM_NAME,
            'payment_date' => $request['payment_date'] ?? '',
            'payment_amount' => $request['payment_amount'] ?? 0,
            'approved_quota' => $request['approved_quota'] ?? 0,
            'status' => $request['status'] ?? self::PAID_STATUS,
            'ksm_reference_number' =>  $request['ksm_reference_number'] ?? '',
            'payment_reference_number' =>  $request['payment_reference_number'] ?? '',
            'approval_number' =>  $request['approval_number'] ?? '',
            'new_ksm_reference_number' =>  $request['new_ksm_reference_number'] ?? '',
            'remarks' =>  $request['remarks'] ?? '',
            'created_by' =>  $request['created_by'] ?? 0,
            'modified_by' =>  $request['created_by'] ?? 0
        ]);
    }

    /**
     * update levy
     *
     * @param array $request The request data containing the application_id, payment_date, payment_amount, approved_quota, status,  ksm_reference_number, payment_reference_number, approval_number, new_ksm_reference_number, remarks, created_by key.
     * @param object $levyDetails levyDetails object
     * 
     * @return void
     */
    private function updateLevy($request, $levyDetails)
    {
        $levyDetails->payment_date              = $request['payment_date'] ?? $levyDetails->payment_date;
        $levyDetails->payment_amount            = $request['payment_amount'] ?? $levyDetails->payment_amount;
        $levyDetails->approved_quota            = $request['approved_quota'] ?? $levyDetails->approved_quota;
        $levyDetails->status                    = $request['status'] ?? $levyDetails->status;
        $levyDetails->ksm_reference_number      = $request['ksm_reference_number'] ?? $levyDetails->ksm_reference_number;
        $levyDetails->payment_reference_number  = $request['payment_reference_number'] ?? $levyDetails->payment_reference_number;
        $levyDetails->approval_number           = $request['approval_number'] ?? $levyDetails->approval_number;
        $levyDetails->new_ksm_reference_number  = $request['new_ksm_reference_number'] ?? $levyDetails->new_ksm_reference_number;
        $levyDetails->remarks                   = $request['remarks'] ?? $levyDetails->remarks;
        $levyDetails->modified_by               = $request['modified_by'] ?? $levyDetails->modified_by;
        $levyDetails->save();
    }

    /**
     * Update Application Summary Status
     *
     * @param array $request The request data containing the ksm_reference_number key.
     * 
     * @return void
     */
    private function updateApplicationSummaryStatus($request)
    {
        $request['ksm_reference_number'] = $request['ksm_reference_number'] ?? '';
        $request['status'] = $request['status'] ?? self::PAID_STATUS;
        $request['action'] = Config::get('services.APPLICATION_SUMMARY_ACTION')[5];
        $this->applicationSummaryServices->ksmUpdateStatus($request);
    }

    /**
     * Update the levy status
     *
     * @param array $request The request data containing the status update data.
     * 
     * @return void
     */
    private function updateLevyStatus($request)
    {
        $applicationDetails = $this->directrecruitmentApplications->findOrFail($request['application_id']);
         if(($applicationDetails->status <= Config::get('services.LEVY_COMPLETED')) || $applicationDetails->status == Config::get('services.FWCMS_REJECTED')){
                $applicationDetails->status = Config::get('services.LEVY_COMPLETED');
            } 
        $applicationDetails->save();

        $request['action'] = Config::get('services.APPLICATION_SUMMARY_ACTION')[5];
        $request['status'] = self::COMPLETED_STATUS;
        $this->applicationSummaryServices->updateStatus($request);
    }

    /**
     * Add expense
     *
     * @param array $request The request data containing the expense data.
     * 
     * @return void
     */
    private function addExpense($request)
    {
        $request['expenses_application_id'] = $request['application_id'] ?? 0;
        $request['expenses_title'] = Config::get('services.OTHER_EXPENSES_TITLE')[1];
        $request['expenses_payment_reference_number'] = $request['payment_reference_number'] ?? '';
        $request['expenses_payment_date'] = $request['payment_date'] ?? '';
        $request['expenses_amount'] = $request['payment_amount'] ?? 0;
        $request['expenses_remarks'] = $request['remarks'] ?? '';
        $this->directRecruitmentExpensesServices->addOtherExpenses($request);
    }

    /** List the Levy
     * 
     * @param $request The request data containing the company_id and application_id key.
     * 
     * @return mixed Returns the paginated list of levy.
     */
    public function list($request): mixed
    {
        return $this->levy->join('directrecruitment_applications', function ($join) use($request) {
            $join->on('directrecruitment_applications.id', '=', 'levy.application_id')
            ->whereIn('directrecruitment_applications.company_id', $request['company_id']);
            })
            ->where('levy.application_id', $request['application_id'])
            ->select('levy.id', 'levy.application_id', 'levy.item', 'levy.payment_date', 'levy.payment_amount', 'levy.approved_quota', 'levy.status', \DB::raw('(CASE WHEN levy.status = "Paid" THEN "1" ELSE "0" END) AS edit_application'))
            ->orderBy('levy.id', 'desc')
            ->paginate(Config::get('services.paginate_row'));
    }
    /**
     * Show the Levy detail
     * 
     * @param $request The request data containing the company_id and application_id, id key.
     * 
     * @return mixed Returns the levy data
     */
    public function show($request): mixed
    {
        return $this->levy
            ->join('directrecruitment_applications', function ($join) use($request) {
                $join->on('directrecruitment_applications.id', '=', 'levy.application_id')
                ->whereIn('directrecruitment_applications.company_id', $request['company_id']);
            })
            ->select('levy.id', 'levy.application_id', 'levy.item', 'levy.payment_date', 'levy.payment_amount', 'levy.approved_quota', 'levy.status', 'levy.ksm_reference_number', 'levy.payment_reference_number', 'levy.approval_number', 'levy.new_ksm_reference_number', 'levy.remarks', 'levy.created_by', 'levy.modified_by', 'levy.created_at', 'levy.updated_at', 'levy.deleted_at', \DB::raw('(CASE WHEN levy.status = "Paid" THEN "1" ELSE "0" END) AS edit_application'))
            ->find($request['id']);
    }

    /**
     * Create the Levy
     * 
     * @param $request The request data containing the create levy data
     * 
     * @return bool|array An array of validation errors or boolean based on the processing result
     */
    public function create($request): bool|array
    {
        $validationResult = $this->validateCreateRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }

        $applicationCheck = $this->getApplication($request);
        if($applicationCheck->company_id != $request['company_id']) {
            return self::ERROR_INVALID_USER;
        }
        $approvedInterviewQuota = $this->getApplicationInterviewApprovedQuota($request);
        if($request['approved_quota'] > $approvedInterviewQuota) {
            return self::ERROR_QUOTA;
        }
        $this->createLevy($request);

        $this->updateApplicationSummaryStatus($request);

        $ksmCount = $this->getKsmCount($request);
        $levyPaidCount = $this->levyPaidCount($request);

        //if($ksmCount == $levyPaidCount) {
            $this->updateLevyStatus($request);
        //}
        $this->addExpense($request);
        
        return true;
    }

    /**
     * Update the Levy
     * 
     * @param $request The request data containing the update levy data
     * 
     * @return bool|array An array of validation errors or boolean based on the processing result
     */
    public function update($request): bool|array
    {
        $validationResult = $this->validateUpdateRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }

        $levyDetails = $this->levy->findOrFail($request['id']);
        $applicationCheck = $this->getApplication($request);
        if($applicationCheck->company_id != $request['company_id']) {
            return self::ERROR_INVALID_USER;
        } else if($request['application_id'] != $levyDetails->application_id) {
            return self::ERROR_INVALID_USER;
        }
        $approvedInterviewQuota = $this->getApplicationInterviewApprovedQuota($request);
        if($request['approved_quota'] > $approvedInterviewQuota) {
            return self::ERROR_QUOTA;
        }

        $this->updateLevy($request, $levyDetails);

        $ksmCount = $this->getKsmCountByStatus($request);
        $levyPaidCount = $this->levyPaidCount($request);                
        //if($ksmCount == $levyPaidCount) {
            $this->updateLevyStatus($request);
        //}
        return true;
    }
}