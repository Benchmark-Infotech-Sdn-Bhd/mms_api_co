<?php

namespace App\Services;

use App\Models\ApplicationInterviews;
use App\Models\ApplicationInterviewAttachments;
use App\Models\FWCMS;
use App\Models\DirectrecruitmentApplications;
use App\Models\Levy;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

class ApplicationInterviewsServices
{
    /**
     * @var ApplicationInterviews
     */
    private ApplicationInterviews $applicationInterviews;

    /**
     * @var ApplicationInterviewAttachments
     */
    private ApplicationInterviewAttachments $applicationInterviewAttachments;

    /**
     * @var FWCMS
     */
    private FWCMS $fwcms;

    /**
     * @var DirectrecruitmentApplications
     */
    private DirectrecruitmentApplications $directrecruitmentApplications;

    /**
     * @var Levy
     */
    private Levy $levy;

    /**
     * @var Storage
     */
    private Storage $storage;
    /**
     * @var ApplicationSummaryServices
     */
    private ApplicationSummaryServices $applicationSummaryServices;

    /**
     * ApplicationInterviews Constructor
     * @param ApplicationInterviews $applicationInterviews
     * @param ApplicationInterviewAttachments $applicationInterviewAttachments
     * @param FWCMS $fwcms
     * @param DirectrecruitmentApplications $directrecruitmentApplications
     * @param Levy $levy
     * @param ApplicationSummaryServices $applicationSummaryServices;
     */
    public function __construct(ApplicationInterviews $applicationInterviews, ApplicationInterviewAttachments $applicationInterviewAttachments,  DirectrecruitmentApplications $directrecruitmentApplications, Levy $levy,  Storage $storage, FWCMS $fwcms, ApplicationSummaryServices $applicationSummaryServices)
    {
        $this->applicationInterviews = $applicationInterviews;
        $this->applicationInterviewAttachments = $applicationInterviewAttachments;
        $this->directrecruitmentApplications = $directrecruitmentApplications;
        $this->levy = $levy;
        $this->storage = $storage;
        $this->fwcms = $fwcms;
        $this->applicationSummaryServices = $applicationSummaryServices;
    }

    /**
     * @return array
     */
    public function createValidation(): array
    {
        return
            [
                'application_id' => 'required',
                'ksm_reference_number' => 'required|unique:application_interviews',
                'schedule_date' => 'required|date|date_format:Y-m-d|after:yesterday',
                'status' => 'required'                
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
                'ksm_reference_number' => 'required|unique:application_interviews,ksm_reference_number,'.$param['id'],
                'schedule_date' => 'required|date|date_format:Y-m-d|after:yesterday',
                'status' => 'required'
            ];
    }

    /**
     * @param $request
     * @return mixed
     */
    public function list($request): mixed
    {
        return $this->applicationInterviews->where('application_id', $request['application_id'])
        ->select('id', 'ksm_reference_number', 'item_name', 'schedule_date', 'approved_quota', 'approval_date', 'status', 'remarks', 'updated_at')
        ->orderBy('id', 'desc')
        ->paginate(Config::get('services.paginate_row'));
    }

    /**
     * @param $request
     * @return mixed
     */
    public function show($request): mixed
    {
        return $this->applicationInterviews->where('id', $request['id'])->with('applicationInterviewAttachments')
                ->first(['id', 'ksm_reference_number', 'item_name', 'schedule_date', 'approved_quota', 'approval_date', 'status', 'remarks']);
    }

    /**
     * @param $request
     * @return bool|array
     */
    public function create($request): bool|array
    {
        $validator = Validator::make($request->toArray(), $this->createValidation());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }
        
        $fwcmsQuota = $this->fwcms->where('ksm_reference_number', $request['ksm_reference_number'])->sum('applied_quota');
        if($request['approved_quota'] > $fwcmsQuota) {
            return [
                'quotaError' => true
            ];
        }

        $applicationInterview = $this->applicationInterviews->create([
            'application_id' => $request['application_id'] ?? 0,
            'item_name' => Config::get('services.APPLICATION_INTERVIEW_ITEM_NAME'),
            'ksm_reference_number' => $request['ksm_reference_number'] ?? '',
            'schedule_date' => $request['schedule_date'] ?? '',
            'approved_quota' => !empty($request['approved_quota']) ? ($request['approved_quota'] ?? 0) : 0,
            'approval_date' => !empty($request['approval_date']) ? ($request['approval_date'] ?? null) : null,
            'status' => $request['status'] ?? '',            
            'remarks' => $request['remarks'] ?? '',
            'created_by' =>  $request['created_by'] ?? 0,
            'modified_by' =>  $request['modified_by'] ?? 0
        ]);

        if (request()->hasFile('attachment')){
            foreach($request->file('attachment') as $file){
                $fileName = $file->getClientOriginalName();
                $filePath = '/directRecruitment/interviews/' . $fileName; 
                $linode = $this->storage::disk('linode');
                $linode->put($filePath, file_get_contents($file));
                $fileUrl = $this->storage::disk('linode')->url($filePath);
                $this->applicationInterviewAttachments::create([
                        "file_id" => $applicationInterview['id'],
                        "file_name" => $fileName,
                        "file_type" => 'proposal',
                        "file_url" =>  $fileUrl         
                    ]);  
            }
        }

        $request['ksm_reference_number'] = $request['ksm_reference_number'] ?? '';
        $request['status'] = $request['status'] ?? '';
        $request['action'] = Config::get('services.APPLICATION_SUMMARY_ACTION')[4];
        $this->applicationSummaryServices->ksmUpdateStatus($request);

        return true;
    }

    /**
     * @param $request
     * @return bool|array
     */
    public function update($request): bool|array
    {
        $validator = Validator::make($request->toArray(), $this->updateValidation($request));
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }

        $fwcmsQuota = $this->fwcms->where('ksm_reference_number', $request['ksm_reference_number'])->sum('applied_quota');
        if($request['approved_quota'] > $fwcmsQuota) {
            return [
                'quotaError' => true
            ];
        }

        $applicationInterviewsDetails = $this->applicationInterviews->findOrFail($request['id']);

        $applicationInterviewsDetails->application_id = $request['application_id'] ?? $applicationInterviewsDetails->application_id;
        $applicationInterviewsDetails->ksm_reference_number = $request['ksm_reference_number'] ?? $applicationInterviewsDetails->ksm_reference_number;
        $applicationInterviewsDetails->item_name = $request['item_name'] ?? $applicationInterviewsDetails->item_name;
        $applicationInterviewsDetails->schedule_date = $request['schedule_date'] ?? $applicationInterviewsDetails->schedule_date;
        $applicationInterviewsDetails->approved_quota        = !empty($request['approved_quota']) ? ($request['approved_quota'] ?? $applicationInterviewsDetails->approved_quota) : $applicationInterviewsDetails->approved_quota;
        $applicationInterviewsDetails->approval_date        = !empty($request['approval_date']) ? ($request['approval_date'] ?? $applicationInterviewsDetails->approval_date) : $applicationInterviewsDetails->approval_date;
        $applicationInterviewsDetails->status               = $request['status'] ?? $applicationInterviewsDetails->status;        
        $applicationInterviewsDetails->remarks              = $request['remarks'] ?? $applicationInterviewsDetails->remarks;
        $applicationInterviewsDetails->modified_by          = $request['modified_by'] ?? $applicationInterviewsDetails->modified_by;
        $applicationInterviewsDetails->save();

        $request['ksm_reference_number'] = $request['ksm_reference_number'] ?? $applicationInterviewsDetails->ksm_reference_number;
        $request['status'] = $request['status'] ?? $applicationInterviewsDetails->status;
        $request['action'] = Config::get('services.APPLICATION_SUMMARY_ACTION')[4];
        $this->applicationSummaryServices->ksmUpdateStatus($request);
        
        
        $ksmCount = $this->fwcms->where('application_id', $request['application_id'])
                    ->where('status', '!=', 'Rejected')
                    ->count('ksm_reference_number');
        $applicationInterviewApprovedCount = $this->applicationInterviews->where('application_id', $request['application_id'])
                        ->where('status', 'Approved')
                        ->count();
        if($ksmCount == $applicationInterviewApprovedCount) {
            $applicationDetails = $this->directrecruitmentApplications->findOrFail($request['application_id']);
            $applicationDetails->status = Config::get('services.INTERVIEW_COMPLETED');
            $applicationDetails->save();

            $request['action'] = Config::get('services.APPLICATION_SUMMARY_ACTION')[4];
            $request['status'] = 'Completed';
            $this->applicationSummaryServices->updateStatus($request);
        }

        if (request()->hasFile('attachment')){

            $this->applicationInterviewAttachments->where('file_id', $request['id'])->delete();

            foreach($request->file('attachment') as $file){
                $fileName = $file->getClientOriginalName();
                $filePath = '/directRecruitment/interviews/' . $fileName; 
                $linode = $this->storage::disk('linode');
                $linode->put($filePath, file_get_contents($file));
                $fileUrl = $this->storage::disk('linode')->url($filePath);
                $this->applicationInterviewAttachments::create([
                    "file_id" => $request['id'],
                    "file_name" => $fileName,
                    "file_type" => 'proposal',
                    "file_url" =>  $fileUrl         
                ]);  
            }
        }

        return true;
    }

    /**
     *
     * @param $request
     * @return mixed
     */    
    public function deleteAttachment($request): mixed
    {   
        $data = $this->applicationInterviewAttachments::find($request['id']); 
        if(is_null($data)){
            return [
                "isDeleted" => false,
                "message" => "Data not found"
            ];
        }
        return [
            "isDeleted" => $data->delete(),
            "message" => "Deleted Successfully"
        ];
    }

    /**
     * @return mixed
     */
    public function dropdownKsmReferenceNumber($request): mixed
    {
        if(isset($request['application_type']) && !empty($request['application_type'])){

            switch ($request['application_type']) {
            case 'FWCMS':
            case 'INTERVIEW':
                return $this->fwcms::where('application_id', $request['id'])->whereIn('status', Config::get('services.APPLICATION_INTERVIEW_KSM_REFERENCE_STATUS'))->select('id','ksm_reference_number')->orderBy('created_at','DESC')->get();
                break;

            case 'LEVY':
                return $this->applicationInterviews::where('application_id', $request['id'])->whereIn('status', Config::get('services.APPLICATION_INTERVIEW_KSM_REFERENCE_STATUS'))->select('id','ksm_reference_number')->orderBy('created_at','DESC')->get();
                break;

            case 'APPROVAL':
                return $this->levy::where('application_id', $request['id'])->whereIn('status', Config::get('services.APPLICATION_LEVY_KSM_REFERENCE_STATUS'))->select('id','ksm_reference_number')->orderBy('created_at','DESC')->get();
                break;

            }
        }
        
    }
}