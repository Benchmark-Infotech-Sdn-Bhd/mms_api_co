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
     * FWCMSServices Constructor
     * @param FWCMS $fwcms
     * @param DirectrecruitmentApplications $directrecruitmentApplications
     * @param ApplicationSummaryServices $applicationSummaryServices;
     * @param Levy $levy
     * @param ApplicationInterviews $applicationInterviews
     * @param DirectRecruitmentApplicationApproval $directRecruitmentApplicationApproval
     */
    public function __construct(FWCMS $fwcms, DirectrecruitmentApplications $directrecruitmentApplications, ApplicationSummaryServices $applicationSummaryServices, Levy $levy, ApplicationInterviews $applicationInterviews, DirectRecruitmentApplicationApproval $directRecruitmentApplicationApproval)
    {
        $this->fwcms = $fwcms;
        $this->directrecruitmentApplications = $directrecruitmentApplications;
        $this->applicationSummaryServices = $applicationSummaryServices;
        $this->levy = $levy;
        $this->applicationInterviews = $applicationInterviews;
        $this->directRecruitmentApplicationApproval = $directRecruitmentApplicationApproval;
    }
    /**
     * @return array
     */
    public function createValidation(): array
    {
        return
            [
                'application_id' => 'required',
                'submission_date' => 'required|date|date_format:Y-m-d|before:tomorrow',
                'applied_quota' => 'required|regex:/^[0-9]+$/|max:3',
                'status' => 'required',
                'ksm_reference_number' => 'required|regex:/^[a-zA-Z0-9\/]*$/|max:21'
            ];
    }
     /**
     * @return array
     */
    public function updateValidation(): array
    {
        return
            [
                'id' => 'required',
                'application_id' => 'required',
                'submission_date' => 'required|date|date_format:Y-m-d|before:tomorrow',
                'applied_quota' => 'required|regex:/^[0-9]+$/|max:3',
                'status' => 'required',
                'ksm_reference_number' => 'required|regex:/^[a-zA-Z0-9\/]*$/|max:21'
            ];
    }
    /**
     * @param $request
     * @return mixed
     */
    public function list($request): mixed
    {
        return $this->fwcms->where('application_id', $request['application_id'])
        ->select('id', 'application_id', 'submission_date', 'applied_quota', 'status', 'ksm_reference_number', 'updated_at')
        ->orderBy('id', 'desc')
        ->paginate(Config::get('services.paginate_row'));
    }
    /**
     * @param $request
     * @return mixed
     */
    public function show($request): mixed
    {
        return $this->fwcms->where('id', $request['id'])
                ->first(['id', 'application_id', 'submission_date', 'applied_quota', 'status', 'ksm_reference_number', 'remarks']);
    }
    /**
     * @param $request
     * @return bool|array
     */
    public function create($request): bool|array
    {
        $validator = Validator::make($request, $this->createValidation());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }
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
        $applicationDetails = $this->directrecruitmentApplications->findOrFail($request['application_id']);
        if ($applicationDetails->status == Config::get('services.FWCMS_REJECTED')) {
            $applicationDetails->status = Config::get('services.CHECKLIST_COMPLETED');
        }
        return true;
    }
    /**
     * @param $request
     * @return bool|array
     */
    public function update($request): bool|array
    {
        $validator = Validator::make($request, $this->updateValidation());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }
        
        $applicationDetails = $this->directrecruitmentApplications->findOrFail($request['application_id']);
        $fwcmsDetails = $this->fwcms->findOrFail($request['id']);
        if($applicationDetails->status >= Config::get('services.LEVY_COMPLETED'))
        {
            return [
                'processError' => true
            ];
        } else {
            $ksmReferenceNumbers = $this->levy->levyKSM($request['application_id']);
            if(count($ksmReferenceNumbers) > 0) {
                if(in_array($fwcmsDetails->ksm_reference_number, $ksmReferenceNumbers)) {
                    return [
                        'processError' => true
                    ];
                } else {
                    $interviewDetails = $this->applicationInterviews->where('ksm_reference_number', $fwcmsDetails->ksm_reference_number)
                                        ->where('application_id', $request['application_id'])
                                        ->select('id')
                                        ->first();
                    if(!empty($interviewDetails)) {
                        $this->applicationInterviews->where('id', $interviewDetails->id)->update(['ksm_reference_number' => $request['ksm_reference_number']]);
                    }
                }
            }
        }
        $fwcmsDetails->application_id       = $request['application_id'] ?? $fwcmsDetails->application_id;
        $fwcmsDetails->submission_date      = $request['submission_date'] ?? $fwcmsDetails->submission_date;
        $fwcmsDetails->applied_quota        = $request['applied_quota'] ?? $fwcmsDetails->applied_quota;
        $fwcmsDetails->status               = $request['status'] ?? $fwcmsDetails->status;
        $fwcmsDetails->ksm_reference_number = $request['ksm_reference_number'] ?? $fwcmsDetails->ksm_reference_number;
        $fwcmsDetails->remarks              = $request['remarks'] ?? $fwcmsDetails->remarks;
        $fwcmsDetails->modified_by          = $request['modified_by'] ?? $fwcmsDetails->modified_by;
        $fwcmsDetails->save();

        $fwcmsCount = $this->fwcms->where('application_id', $request['application_id'])->count();
        $fwcmsRejectedCount = $this->fwcms->where('application_id', $request['application_id'])
                        ->where('status', 'Rejected')
                        ->count();
        $fwcmsApprovedCount = $this->fwcms->where('application_id', $request['application_id'])
                        ->where('status', 'Approved')
                        ->count();
        if($request['status'] == 'Rejected') {
            $approvalCount = $this->directRecruitmentApplicationApproval->where('application_id', $request['application_id'])->count('id');
            if($approvalCount > 0) {
                if($fwcmsCount == ($fwcmsApprovedCount + $fwcmsRejectedCount)) {
                    $applicationDetails->status = Config::get('services.APPROVAL_COMPLETED');
                    $applicationDetails->save();
                }
            }
        }
        $request['ksm_reference_number'] = $request['ksm_reference_number'] ?? $fwcmsDetails->ksm_reference_number;
        $request['status'] = $request['status'] ?? $fwcmsDetails->status;
        $request['action'] = Config::get('services.APPLICATION_SUMMARY_ACTION')[3];
        $this->applicationSummaryServices->ksmUpdateStatus($request);

        if($fwcmsCount == $fwcmsRejectedCount) {
            $applicationDetails->status = Config::get('services.FWCMS_REJECTED');
            $applicationDetails->save();
        }
        if($fwcmsCount == $fwcmsApprovedCount) {
            $applicationDetails->status = Config::get('services.FWCMS_COMPLETED');
            $applicationDetails->save();

            $request['action'] = Config::get('services.APPLICATION_SUMMARY_ACTION')[3];
            $request['status'] = 'FWCMS Completed';
            $this->applicationSummaryServices->updateStatus($request);
        }
        return true;
    }
}