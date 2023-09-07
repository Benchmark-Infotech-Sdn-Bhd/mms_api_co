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
                'ksm_reference_number' => 'required|regex:/^[a-zA-Z0-9\/]*$/|max:21|unique:fwcms'
            ];
    }
     /**
     * @return array
     */
    public function updateValidation($param): array
    {
        return
            [
                'id' => 'required',
                'application_id' => 'required',
                'submission_date' => 'required|date|date_format:Y-m-d|before:tomorrow',
                'applied_quota' => 'required|regex:/^[0-9]+$/|max:3',
                'status' => 'required',
                'ksm_reference_number' => 'required|regex:/^[a-zA-Z0-9\/]*$/|max:21|unique:fwcms,ksm_reference_number,'.$param['id']
            ];
    }
    /**
     * @param $request
     * @return mixed
     */
    public function list($request): mixed
    {
        return $this->fwcms
        ->leftJoin('levy', function($join) use ($request){
            $join->on('levy.application_id', '=', 'fwcms.application_id')
            ->on('levy.ksm_reference_number', '=', 'fwcms.ksm_reference_number');
          })
        ->where('fwcms.application_id', $request['application_id'])
        ->select('fwcms.id', 'fwcms.application_id', 'fwcms.submission_date', 'fwcms.applied_quota', 'fwcms.status', 'fwcms.ksm_reference_number', 'fwcms.updated_at', \DB::raw('(CASE WHEN levy.status = "Paid" THEN "1" ELSE "0" END) AS edit_application'))
        ->orderBy('id', 'desc')
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
            $join->on('levy.application_id', '=', 'fwcms.application_id')
            ->on('levy.ksm_reference_number', '=', 'fwcms.ksm_reference_number');
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
        $validator = Validator::make($request, $this->createValidation());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }

        $proposalQuota = $this->directrecruitmentApplications->where('id', $request['application_id'])->sum('quota_applied');
        $fwcmsQuota = $this->fwcms
        ->where('application_id', $request['application_id'])
        ->where('status', '<>' , 'Rejected')
        ->sum('applied_quota');
        $fwcmsQuota += $request['applied_quota'];
        if($fwcmsQuota > $proposalQuota) {
            return [
                'quotaError' => true
            ];
        }
        $applicationDetails = $this->directrecruitmentApplications->findOrFail($request['application_id']);
        if($applicationDetails->status == Config::get('services.APPROVAL_COMPLETED')) {
            return [
                'processError' => true
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
        /* if($applicationDetails->status != Config::get('services.APPROVAL_COMPLETED')){
            $applicationDetails->status = Config::get('services.CHECKLIST_COMPLETED');
            $applicationDetails->save();
        } */

        if($applicationDetails->status <= Config::get('services.CHECKLIST_COMPLETED')){
            $applicationDetails->status = Config::get('services.CHECKLIST_COMPLETED');
            $applicationDetails->save();
        }        

        $request['ksm_reference_number'] = $request['ksm_reference_number'] ?? '';
        $request['status'] = $request['status'] ?? '';
        $request['action'] = Config::get('services.APPLICATION_SUMMARY_ACTION')[3];
        $this->applicationSummaryServices->ksmUpdateStatus($request);

        return true;
    }
    /**
     * @param $request
     * @return bool|array
     */
    public function update($request): bool|array
    {
        $validator = Validator::make($request, $this->updateValidation($request));
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }

        $proposalQuota = $this->directrecruitmentApplications->where('id', $request['application_id'])->sum('quota_applied');
        $fwcmsQuota = $this->fwcms
        ->where('application_id', $request['application_id'])
        ->where('status', '<>' , 'Rejected')
        ->where('id', '<>' , $request['id'])
        ->sum('applied_quota');
        $fwcmsQuota += $request['applied_quota'];
        if($fwcmsQuota > $proposalQuota) {
            return [
                'quotaError' => true
            ];
        }
        
        $applicationDetails = $this->directrecruitmentApplications->findOrFail($request['application_id']);
        $fwcmsDetails = $this->fwcms->findOrFail($request['id']);
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
        //if($fwcmsCount == $fwcmsApprovedCount) {
            /* if($applicationDetails->status != Config::get('services.APPROVAL_COMPLETED')){
                $applicationDetails->status = Config::get('services.FWCMS_COMPLETED');
            }
            $applicationDetails->save(); */

            if($applicationDetails->status <= Config::get('services.FWCMS_COMPLETED')){
                $applicationDetails->status = Config::get('services.FWCMS_COMPLETED');
            } 
            $applicationDetails->save();

            $request['action'] = Config::get('services.APPLICATION_SUMMARY_ACTION')[3];
            $request['status'] = 'Completed';
            $this->applicationSummaryServices->updateStatus($request);
        //}
        return true;
    }
}