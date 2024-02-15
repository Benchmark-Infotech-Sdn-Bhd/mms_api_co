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
     * @param $request
     * @return mixed
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
     * @param $request
     * @return bool|array
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
     * @param $request
     * @return bool|array
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

    private function applyLevyTableFilter($join, $request)
    {
        $join->on('levy.application_id', '=', 'fwcms.application_id')->on('levy.ksm_reference_number', '=', 'fwcms.ksm_reference_number');
    }

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

    private function findDirectrecruitmentApplications($request)
    {
        return $this->directrecruitmentApplications->findOrFail($request['application_id']);
    }

    private function getCountDirectrecruitmentApplicationsQuotaApplied($request)
    {
        return $this->directrecruitmentApplications->where('id', $request['application_id'])->sum('quota_applied');
    }

    private function getFwcmsAppliedQuotaCount($request)
    {
        return $this->fwcms
            ->where('application_id', $request['application_id'])
            ->where('status', '<>' , self::STATUS_REJECTED)
            ->sum('applied_quota');
    }

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

    private function updateDirectrecruitmentApplicationStatus($applicationDetails)
    {
        if(($applicationDetails->status <= Config::get('services.CHECKLIST_COMPLETED')) || $applicationDetails->status == Config::get('services.FWCMS_REJECTED')) {
            $applicationDetails->status = Config::get('services.CHECKLIST_COMPLETED');
            $applicationDetails->save();
        }
    }

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

    private function findFwcms($request)
    {
        return $this->fwcms->find($request['id']);
    }

    private function getFwcmsAppliedApplicationQuotaCount($request)
    {
        return $this->fwcms
            ->where('application_id', $request['application_id'])
            ->where('status', '<>' , self::STATUS_REJECTED)
            ->where('id', '<>' , $request['id'])
            ->sum('applied_quota');
    }

    private function showLevyKSM($request)
    {
        return $this->levy->levyKSM($request['application_id']); 
    }

    private function showApplicationInterviews($request)
    {
        return $this->applicationInterviews->where('ksm_reference_number', $request['ksm_reference_number'])
            ->where('application_id', $request['application_id'])
            ->select('id')
            ->first();
    }

    private function updateApplicationInterviews($request, $interviewDetailsId)
    {
        $this->applicationInterviews->where('id', $interviewDetailsId)->update(['ksm_reference_number' => $request['ksm_reference_number']]);
    }

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

    private function showFwcmsApplicationCount($request)
    {
        return $this->fwcms->where('application_id', $request['application_id'])->count();
    }

    private function showFwcmsRejectedApplicationCount($request)
    {
        return $this->fwcms->where('application_id', $request['application_id'])
            ->where('status', self::STATUS_REJECTED)
            ->count();
    }

    private function showFwcmsApprovedApplicationCount($request)
    {
        return $this->fwcms->where('application_id', $request['application_id'])
            ->where('status', self::STATUS_APPROVED)
            ->count();   
    }

    private function updateApplicationSummaryKsmStatus($fwcmsDetails, $request)
    {
        $request['ksm_reference_number'] = $request['ksm_reference_number'] ?? $fwcmsDetails->ksm_reference_number;
        $request['status'] = $request['status'] ?? $fwcmsDetails->status;
        $request['action'] = Config::get('services.APPLICATION_SUMMARY_ACTION')[3];
        $this->applicationSummaryServices->ksmUpdateStatus($request);
    }

    private function updateApplicationSummaryStatus($request)
    {
        $request['action'] = Config::get('services.APPLICATION_SUMMARY_ACTION')[3];
        $request['status'] = self::STATUS_COMPLETED;
        $this->applicationSummaryServices->updateStatus($request);
    }

    private function updateFwcmsApplicationApprovedStatus($applicationDetails, $request)
    {
        if($request['status'] == self::STATUS_APPROVED) {
            if(($applicationDetails->status <= Config::get('services.FWCMS_COMPLETED'))  || $applicationDetails->status == Config::get('services.FWCMS_REJECTED')) {
                $applicationDetails->status = Config::get('services.FWCMS_COMPLETED');
                $applicationDetails->save();
            }             
        }
    }

    private function updateFwcmsApplicationRejectedStatus($applicationDetails, $request, $fwcmsCount, $fwcmsRejectedCount)
    {
        if($request['status'] == self::STATUS_REJECTED) {
            if($fwcmsCount == $fwcmsRejectedCount) {
                $applicationDetails->status = Config::get('services.FWCMS_REJECTED');
                $applicationDetails->save();
            }
        }
    }

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

    private function showDirectRecruitmentApplicationApprovalApplication($request)
    {
        return $this->directRecruitmentApplicationApproval->where('application_id', $request['application_id'])->count('id');
    }
}