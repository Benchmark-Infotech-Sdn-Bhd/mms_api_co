<?php

namespace App\Services;

use App\Models\FWCMS;
use App\Models\DirectrecruitmentApplications;
use App\Models\Levy;
use App\Models\ApplicationInterviews;
use App\Models\DirectRecruitmentApplicationApproval;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Config;

class FWCMSServices
{
    public const STATUS_APPROVED = 'Approved';
    public const STATUS_REJECTED = 'Rejected';
    public const STATUS_COMPLETED = 'Completed';

    public const ERROR_INVALID_USER = ['InvalidUser' => true];
    public const ERROR_QUOTA = ['quotaError' => true];
    public const ERROR_PROCESS = ['processError' => true];

    /**
     * @var FWCMS
     */
    private FWCMS $fwcms;

    /**
     * @var ApplicationSummaryServices
     */
    private ApplicationSummaryServices $applicationSummaryServices;

    /**
     * @var DirectrecruitmentApplications
     */
    private DirectrecruitmentApplications $directrecruitmentApplications;

    /**
     * @var Levy
     */
    private Levy $levy;

    /**
     * @var ApplicationInterviews
     */
    private ApplicationInterviews $applicationInterviews;

    /**
     * @var DirectRecruitmentApplicationApproval
     */
    private DirectRecruitmentApplicationApproval $directRecruitmentApplicationApproval;

    /**
     * Constructor method.
     * 
     * @param FWCMS $fwcms Instance of the FWCMS class.
     * @param DirectrecruitmentApplications $directrecruitmentApplications Instance of the DirectrecruitmentApplications class.
     * @param ApplicationSummaryServices $applicationSummaryServices Instance of the ApplicationSummaryServices class.
     * @param Levy $levy Instance of the Levy class.
     * @param ApplicationInterviews $applicationInterviews Instance of the ApplicationInterviews class.
     * @param DirectRecruitmentApplicationApproval $directRecruitmentApplicationApproval Instance of the DirectRecruitmentApplicationApproval class.
     * 
     * @return void
     */
    public function __construct(
        FWCMS                                    $fwcms,
        DirectrecruitmentApplications            $directrecruitmentApplications,
        ApplicationSummaryServices               $applicationSummaryServices,
        Levy                                     $levy,
        ApplicationInterviews                    $applicationInterviews,
        DirectRecruitmentApplicationApproval     $directRecruitmentApplicationApproval
    )
    {
        $this->fwcms = $fwcms;
        $this->directrecruitmentApplications = $directrecruitmentApplications;
        $this->applicationSummaryServices = $applicationSummaryServices;
        $this->levy = $levy;
        $this->applicationInterviews = $applicationInterviews;
        $this->directRecruitmentApplicationApproval = $directRecruitmentApplicationApproval;
    }

    /**
     * Creates the validation rules for create a new fwcms.
     *
     * @return array The array containing the validation rules.
     */
    public function createValidation(): array
    {
        return [
            'application_id' => 'required',
            'submission_date' => 'required|date|date_format:Y-m-d|before:tomorrow',
            'applied_quota' => 'required|regex:/^[0-9]+$/|max:3',
            'status' => 'required',
            'ksm_reference_number' => 'required|regex:/^[a-zA-Z0-9\/]*$/|max:21|unique:fwcms'
        ];
    }
    
    /**
     * Creates the validation rules for updating the fwcms.
     *
     * @return array The array containing the validation rules.
     */
    public function updateValidation($param): array
    {
        return [
            'id' => 'required',
            'application_id' => 'required',
            'submission_date' => 'required|date|date_format:Y-m-d|before:tomorrow',
            'applied_quota' => 'required|regex:/^[0-9]+$/|max:3',
            'status' => 'required',
            'ksm_reference_number' => 'required|regex:/^[a-zA-Z0-9\/]*$/|max:21|unique:fwcms,ksm_reference_number,'.$param['id']
        ];
    }
    
    /**
     * Returns a paginated list of fwcms based on the given search request.
     * 
     * @param array $request The search request parameters and company id.
     * @return mixed Returns the paginated list of fwcms.
     */
    public function list($request): mixed
    {
        return $this->fwcms
        ->leftJoin('levy', function($join) use ($request){
            $this->applyLevyTableFilter($join, $request);
        })
        ->join('directrecruitment_applications', function ($join) use($request) {
            $this->applyDirectrecruitmentApplicationsTableFilter($join, $request);
        })
        ->where('fwcms.application_id', $request['application_id'])
        ->select('fwcms.id', 'fwcms.application_id', 'fwcms.submission_date', 'fwcms.applied_quota', 'fwcms.status', 'fwcms.ksm_reference_number', 'fwcms.updated_at', \DB::raw('(CASE WHEN levy.status = "Paid" THEN "1" ELSE "0" END) AS edit_application'))
        ->orderBy('fwcms.id', 'desc')
        ->paginate(Config::get('services.paginate_row'));
    }

    /**
     * Show the fwcms with related levy and directrecruitment applications.
     * 
     * @param array $request The request data containing company id, fwcms ID
     * @return mixed Returns the fwcms with related levy and directrecruitment applications.
     */
    public function show($request): mixed
    {
        return $this->fwcms
        ->leftJoin('levy', function($join) use ($request){
            $this->applyLevyTableFilter($join, $request);
        })
        ->join('directrecruitment_applications', function ($join) use($request) {
            $this->applyDirectrecruitmentApplicationsTableFilter($join, $request);
        })
        ->where('fwcms.id', $request['id'])
        ->first(['fwcms.id', 'fwcms.application_id', 'fwcms.submission_date', 'fwcms.applied_quota', 'fwcms.status', 'fwcms.ksm_reference_number', 'fwcms.remarks', \DB::raw('(CASE WHEN levy.status = "Paid" THEN "1" ELSE "0" END) AS edit_application')]);
    }

    /**
     * Creates a new fwcms from the given request data.
     * 
     * @param array $request The array containing fwcms data.
     * @return bool|array Returns an array with the following keys:
     * - "validate": An array of validation errors, if any.
     * - "InvalidUser" (boolean): A array returns InvalidUser if directrecruitmentApplications is null.
     * - "quotaError": A fwcmsQuota is greater than proposalQuota.
     * - "processError": if application status and approval completed status are equal
     * - "isSubmit": A boolean indicating if the fwcms was successfully created.
     */
    public function create($request): bool|array
    {
        $validationResult = $this->createValidateRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }
        
        $applicationDetails = $this->findDirectrecruitmentApplications($request);
        if ($request['company_id'] != $applicationDetails->company_id) {
            return self::ERROR_INVALID_USER;
        }
        
        $proposalQuota = $this->getCountDirectrecruitmentApplicationsQuotaApplied($request);
        $fwcmsQuota = $this->getFwcmsAppliedQuotaCount($request);
        $fwcmsQuota += $request['applied_quota'];
        if ($fwcmsQuota > $proposalQuota) {
            return self::ERROR_QUOTA;
        }

        if ($applicationDetails->status == Config::get('services.APPROVAL_COMPLETED')) {
            return self::ERROR_PROCESS;
        }
        
        $this->createFwcms($request);

        $this->updateDirectrecruitmentApplicationStatus($applicationDetails);

        $this->updateKsmUpdateStatus($request);

        return true;
    }

    /**
     * Updates the fwcms from the given request data.
     * 
     * @param array $request The array containing fwcms data.
     * @return bool|array Returns an array with the following keys:
     * - "validate": An array of validation errors, if any.
     * - "InvalidUser" (boolean): A array returns InvalidUser if directrecruitmentApplications is null | if application id and fwcms application id are not equal.
     * - "quotaError": A fwcmsQuota is greater than proposalQuota.
     * - "processError": if ksm reference number and fwcms ksm reference number are not equal
     * - "isUpdated" (boolean): Indicates whether the data was updated. Always set to `false`.
     */
    public function update($request): bool|array
    {
        $validationResult = $this->updateValidateRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }

        $applicationDetails = $this->findDirectrecruitmentApplications($request);
        $fwcmsDetails = $this->findFwcms($request);
        if($request['company_id'] != $applicationDetails->company_id) {
            return self::ERROR_INVALID_USER;
        }

        if($request['application_id'] != $fwcmsDetails->application_id) {
            return self::ERROR_INVALID_USER;
        }

        $proposalQuota = $this->getCountDirectrecruitmentApplicationsQuotaApplied($request);
        $fwcmsQuota = $this->getFwcmsAppliedApplicationQuotaCount($request);
        $fwcmsQuota += $request['applied_quota'];
        if($fwcmsQuota > $proposalQuota) {
            return self::ERROR_QUOTA;
        }

        $ksmReferenceNumbers = $this->showLevyKSM($request);
        if(count($ksmReferenceNumbers) > 0) {
            if(in_array($fwcmsDetails->ksm_reference_number, $ksmReferenceNumbers)) {
                return self::ERROR_PROCESS;
            } else {

                $interviewDetails = $this->showApplicationInterviews(['ksm_reference_number' => $fwcmsDetails->ksm_reference_number, 'application_id' => $request['application_id']]);
                if(!empty($interviewDetails)) {
                    $this->updateApplicationInterviews($request, $interviewDetails->id);
                }
            }
        }

        $this->updateFwcms($fwcmsDetails, $request);
        $fwcmsCount = $this->showFwcmsApplicationCount($request);
        $fwcmsRejectedCount = $this->showFwcmsRejectedApplicationCount($request);
        $fwcmsApprovedCount = $this->showFwcmsApprovedApplicationCount($request);
        
        $this->updateFwcmsApplicationApprovalCompletedStatus($applicationDetails, $request, $fwcmsCount, $fwcmsApprovedCount, $fwcmsRejectedCount);
        $this->updateApplicationSummaryKsmStatus($fwcmsDetails, $request);
        $this->updateFwcmsApplicationApprovedStatus($applicationDetails, $request);
        $this->updateFwcmsApplicationRejectedStatus($applicationDetails, $request, $fwcmsCount, $fwcmsRejectedCount);
        $this->updateApplicationSummaryStatus($request);
        
        return true;
    }
    
    /**
     * Apply the "levy" filter to the query
     *
     * @param Illuminate\Database\Query\Builder $join The query builder instance
     * @param array $request
     *
     * @return void
     */
    private function applyLevyTableFilter($join, $request)
    {
        $join->on('levy.application_id', '=', 'fwcms.application_id')->on('levy.ksm_reference_number', '=', 'fwcms.ksm_reference_number');
    }
    
    /**
     * Apply the "directrecruitment applications" filter to the query
     *
     * @param Illuminate\Database\Query\Builder $join The query builder instance
     * @param array $request The request data containing the company id
     *
     * @return void
     */
    private function applyDirectrecruitmentApplicationsTableFilter($join, $request)
    {
        $join->on('directrecruitment_applications.id', '=', 'fwcms.application_id')->whereIn('directrecruitment_applications.company_id', $request['company_id']);
    }

    /**
     * Validate the given request data.
     *
     * @param array $request The request data to be validated.
     * @return array|bool Returns an array with 'error' as key and validation error messages as value if validation fails. | Returns true if validation passes.
     */
    private function createValidateRequest($request): array|bool
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
     * Show the directrecruitment applications.
     * 
     * @param array $request The request data containing application id
     * @return mixed Returns the directrecruitment applications.
     */
    private function findDirectrecruitmentApplications($request)
    {
        return $this->directrecruitmentApplications->findOrFail($request['application_id']);
    }
    
    /**
     * Returns a sum of directrecruitment applications based on the given application id.
     * 
     * @param array $request The request data containing application id
     * @return array Returns a sum of directrecruitment applications.
     */
    private function getCountDirectrecruitmentApplicationsQuotaApplied($request)
    {
        return $this->directrecruitmentApplications->where('id', $request['application_id'])->sum('quota_applied');
    }
    
    /**
     * Returns a count of fwcms based on the given application id.
     * 
     * @param array $request The request data containing application id
     * @return array Returns a count of fwcms.
     */
    private function getFwcmsAppliedQuotaCount($request)
    {
        return $this->fwcms
            ->where('application_id', $request['application_id'])
            ->where('status', '<>' , self::STATUS_REJECTED)
            ->sum('applied_quota');
    }
    
    /**
     * Creates a new fwcms from the given request data.
     * 
     * @param array $request The array containing fwcms data.
     *                      The array should have the following keys:
     *                      - application_id: The application id of the fwcms.
     *                      - submission_date: The submission date of the fwcms.
     *                      - applied_quota: The applied quota of the fwcms.
     *                      - status: The status of the fwcms.
     *                      - ksm_reference_number: The ksm reference number of the fwcms.
     *                      - remarks: The remarks of the fwcms.
     *                      - created_by: The ID of the user who created the fwcms.
     *                      - modified_by: The updated fwcms modified by.
     * 
     * @return void
     */
    private function createFwcms($request)
    {
        $this->fwcms->create([
            'application_id' => $request['application_id'] ?? 0,
            'submission_date' => $request['submission_date'] ?? '',
            'applied_quota' => $request['applied_quota'] ?? 0,
            'status' => $request['status'] ?? '',
            'ksm_reference_number' => $request['ksm_reference_number'] ?? '',
            'remarks' => $request['remarks'] ?? '',
            'created_by' =>  $request['created_by'] ?? 0,
            'modified_by' =>  $request['created_by'] ?? 0
        ]);
    }
    
    /**
     * Updates the fwcms application status with the given request.
     * 
     * @param object $applicationDetails The applicationDetails object to be updated.
     * 
     * @return void
     */
    private function updateDirectrecruitmentApplicationStatus($applicationDetails)
    {
        if(($applicationDetails->status <= Config::get('services.CHECKLIST_COMPLETED')) || $applicationDetails->status == Config::get('services.FWCMS_REJECTED')) {
            $applicationDetails->status = Config::get('services.CHECKLIST_COMPLETED');
            $applicationDetails->save();
        }
    }
    
    /**
     * Updates the ksm status with the given request.
     * 
     * @param array $request The array containing ksm status data.
     *                      The array should have the following keys:
     *                      - ksm_reference_number: The updated ksm reference number.
     *                      - status: The updated status. 
     * @return void
     */
    private function updateKsmUpdateStatus($request)
    {
        $request['ksm_reference_number'] = $request['ksm_reference_number'] ?? '';
        $request['status'] = $request['status'] ?? '';
        $request['action'] = Config::get('services.APPLICATION_SUMMARY_ACTION')[3];
        $this->applicationSummaryServices->ksmUpdateStatus($request);
    }

    /**
     * Validate the given request data.
     *
     * @param array $request The request data to be validated.
     * @return array|bool Returns an array with 'error' as key and validation error messages as value if validation fails. | Returns true if validation passes.
     */
    private function updateValidateRequest($request): array|bool
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
     * Show the fwcms.
     * 
     * @param array $request The request data containing fwcms id
     * @return mixed Returns the fwcms.
     */
    private function findFwcms($request)
    {
        return $this->fwcms->find($request['id']);
    }
    
    /**
     * Returns a sum of fwcms based on the given application id and fwcms id.
     * 
     * @param array $request The request data containing application id, fwcms id
     * @return array Returns a sum of fwcms.
     */
    private function getFwcmsAppliedApplicationQuotaCount($request)
    {
        return $this->fwcms
            ->where('application_id', $request['application_id'])
            ->where('status', '<>' , self::STATUS_REJECTED)
            ->where('id', '<>' , $request['id'])
            ->sum('applied_quota');
    }
    
    /**
     * Show the levy ksm.
     * 
     * @param array $request The request data containing application id
     * @return mixed Returns the levy ksm.
     */
    private function showLevyKSM($request)
    {
        return $this->levy->levyKSM($request['application_id']); 
    }
    
    /**
     * Show the application interviews.
     * 
     * @param array $request The request data containing application id, ksm reference number
     * @return mixed Returns the application interviews.
     */
    private function showApplicationInterviews($request)
    {
        return $this->applicationInterviews->where('ksm_reference_number', $request['ksm_reference_number'])
            ->where('application_id', $request['application_id'])
            ->select('id')
            ->first();
    }
    
    /**
     * Updates the application interviews from the given request data.
     * 
     * @param int $interviewDetailsId The id of the application interview.
     * @param array $request The array containing application data.
     *                      The array should have the following keys:
     *                      - ksm_reference_number: The updated ksm reference number.
     * 
     * @return void
     */
    private function updateApplicationInterviews($request, $interviewDetailsId)
    {
        $this->applicationInterviews->where('id', $interviewDetailsId)->update(['ksm_reference_number' => $request['ksm_reference_number']]);
    }
    
    /**
     * Updates the fwcms with the given request.
     * 
     * @param object $fwcmsDetails The fwcmsDetails object to be updated.
     * @param array $request The array containing fwcms data.
     *                      The array should have the following keys:
     *                      - application_id: The updated application id.
     *                      - submission_date: The updated submission date.
     *                      - applied_quota: The updated applied quota.
     *                      - status: The updated status.
     *                      - ksm_reference_number: The updated ksm reference number.
     *                      - remarks: The updated remarks.
     *                      - modified_by: The updated fwcms modified by.
     * 
     * @return void
     */
    private function updateFwcms($fwcmsDetails, $request)
    {
        $fwcmsDetails->application_id       = $request['application_id'] ?? $fwcmsDetails->application_id;
        $fwcmsDetails->submission_date      = $request['submission_date'] ?? $fwcmsDetails->submission_date;
        $fwcmsDetails->applied_quota        = $request['applied_quota'] ?? $fwcmsDetails->applied_quota;
        $fwcmsDetails->status               = $request['status'] ?? $fwcmsDetails->status;
        $fwcmsDetails->ksm_reference_number = $request['ksm_reference_number'] ?? $fwcmsDetails->ksm_reference_number;
        $fwcmsDetails->remarks              = $request['remarks'] ?? $fwcmsDetails->remarks;
        $fwcmsDetails->modified_by          = $request['modified_by'] ?? $fwcmsDetails->modified_by;
        $fwcmsDetails->save();
    }
    
    /**
     * Returns a count of fwcms based on the given application id.
     * 
     * @param array $request The request data containing application id
     * @return array Returns a sum of count.
     */
    private function showFwcmsApplicationCount($request)
    {
        return $this->fwcms->where('application_id', $request['application_id'])->count();
    }
    
    /**
     * Returns a count of fwcms(Rejected) based on the given application id.
     * 
     * @param array $request The request data containing application id
     * @return array Returns a count of fwcms.
     */
    private function showFwcmsRejectedApplicationCount($request)
    {
        return $this->fwcms->where('application_id', $request['application_id'])
            ->where('status', self::STATUS_REJECTED)
            ->count();
    }
    
    /**
     * Returns a count of fwcms(Approved) based on the given application id.
     * 
     * @param array $request The request data containing application id
     * @return array Returns a count of fwcms.
     */
    private function showFwcmsApprovedApplicationCount($request)
    {
        return $this->fwcms->where('application_id', $request['application_id'])
            ->where('status', self::STATUS_APPROVED)
            ->count();   
    }
    
    /**
     * Updates the application summary ksm status with the given request.
     * 
     * @param object $fwcmsDetails The fwcmsDetails object to be updated.
     * @param array $request The array containing ksm status data.
     *                      The array should have the following keys:
     *                      - ksm_reference_number: The updated ksm reference number.
     *                      - status: The updated status. 
     * @return void
     */
    private function updateApplicationSummaryKsmStatus($fwcmsDetails, $request)
    {
        $request['ksm_reference_number'] = $request['ksm_reference_number'] ?? $fwcmsDetails->ksm_reference_number;
        $request['status'] = $request['status'] ?? $fwcmsDetails->status;
        $request['action'] = Config::get('services.APPLICATION_SUMMARY_ACTION')[3];
        $this->applicationSummaryServices->ksmUpdateStatus($request);
    }
    
    /**
     * Updates the application summary status with the given request.
     * 
     * @param array $request The array containing status data.
     *                      The array should have the following keys:
     *                      - summary_status: The updated summary status. 
     * @return void
     */
    private function updateApplicationSummaryStatus($request)
    {
        $request['action'] = Config::get('services.APPLICATION_SUMMARY_ACTION')[3];
        $request['status'] = self::STATUS_COMPLETED;
        $this->applicationSummaryServices->updateStatus($request);
    }
    
    /**
     * Updates the fwcms application approved status with the given request.
     * 
     * @param object $applicationDetails The applicationDetails object to be updated.
     * @param array $request The array containing status.
     * 
     * @return void
     */
    private function updateFwcmsApplicationApprovedStatus($applicationDetails, $request)
    {
        if($request['status'] == self::STATUS_APPROVED) {
            if(($applicationDetails->status <= Config::get('services.FWCMS_COMPLETED'))  || $applicationDetails->status == Config::get('services.FWCMS_REJECTED')) {
                $applicationDetails->status = Config::get('services.FWCMS_COMPLETED');
                $applicationDetails->save();
            }             
        }
    }
    
    /**
     * Updates the fwcms application rejected status with the given request.
     * 
     * @param object $applicationDetails The applicationDetails object to be updated.
     * @param int $fwcmsCount The count of fwcms.
     * @param int $fwcmsRejectedCount The count of fwcms rejected count.
     * @param array $request The array containing status.
     * 
     * @return void
     */
    private function updateFwcmsApplicationRejectedStatus($applicationDetails, $request, $fwcmsCount, $fwcmsRejectedCount)
    {
        if($request['status'] == self::STATUS_REJECTED) {
            if($fwcmsCount == $fwcmsRejectedCount) {
                $applicationDetails->status = Config::get('services.FWCMS_REJECTED');
                $applicationDetails->save();
            }
        }
    }
    
    /**
     * Updates the fwcms application approval completed status with the given request.
     * 
     * @param object $applicationDetails The applicationDetails object to be updated.
     * @param int $fwcmsCount The count of fwcms.
     * @param int $fwcmsApprovedCount The count of fwcms approved count.
     * @param int $fwcmsRejectedCount The count of fwcms rejected count.
     * @param array $request The array containing status.
     * 
     * @return void
     */
    private function updateFwcmsApplicationApprovalCompletedStatus($applicationDetails, $request, $fwcmsCount, $fwcmsApprovedCount, $fwcmsRejectedCount)
    {
        if($request['status'] == self::STATUS_REJECTED) {
            $approvalCount = $this->showDirectRecruitmentApplicationApprovalApplication($request);
            if($approvalCount > 0) {
                if($fwcmsCount == ($fwcmsApprovedCount + $fwcmsRejectedCount)) {
                    $applicationDetails->status = Config::get('services.APPROVAL_COMPLETED');
                    $applicationDetails->save();
                }
            }
        }
    }
    
    /**
     * Show the direct recruitment application approval.
     * 
     * @param array $request The array containing application id, direct recruitment id.
     * @return mixed Returns the direct recruitment application approval.
     */
    private function showDirectRecruitmentApplicationApprovalApplication($request)
    {
        return $this->directRecruitmentApplicationApproval->where('application_id', $request['application_id'])->count('id');
    }
}