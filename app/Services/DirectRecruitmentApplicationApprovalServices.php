<?php

namespace App\Services;

use App\Models\DirectRecruitmentApplicationApproval;
use App\Models\ApprovalAttachments;
use App\Models\DirectrecruitmentApplications;
use App\Models\FWCMS;
use App\Models\CRMProspectService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

class DirectRecruitmentApplicationApprovalServices
{
    /**
     * @var DirectRecruitmentApplicationApproval
     */
    private DirectRecruitmentApplicationApproval $directRecruitmentApplicationApproval;

    /**
     * @var ApprovalAttachments
     */
    private ApprovalAttachments $approvalAttachments;

    /**
     * @var Storage
     */
    private Storage $storage;

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
     * @var CRMProspectService
     */
    private CRMProspectService $crmProspectService;

    /**
     * DirectRecruitmentApplicationApprovalServices Constructor
     * @param DirectRecruitmentApplicationApproval $directRecruitmentApplicationApproval
     * @param ApprovalAttachments $approvalAttachments
     * @param Storage $storage
     * @param DirectrecruitmentApplications $directrecruitmentApplications
     * @param FWCMS $fwcms;
     * @param ApplicationSummaryServices $applicationSummaryServices;
     * @param CRMProspectService $crmProspectServicez
     */
    public function __construct(DirectRecruitmentApplicationApproval $directRecruitmentApplicationApproval, ApprovalAttachments $approvalAttachments, 
    Storage $storage, DirectrecruitmentApplications $directrecruitmentApplications, FWCMS $fwcms, ApplicationSummaryServices $applicationSummaryServices, CRMProspectService $crmProspectService)
    {
        $this->directRecruitmentApplicationApproval = $directRecruitmentApplicationApproval;
        $this->approvalAttachments = $approvalAttachments;
        $this->storage = $storage;
        $this->directrecruitmentApplications = $directrecruitmentApplications;
        $this->fwcms = $fwcms;
        $this->applicationSummaryServices = $applicationSummaryServices;
        $this->crmProspectService = $crmProspectService;
    }
    /**
     * @return array
     */
    public function createValidation(): array
    {
        return
            [
                'application_id' => 'required',
                'ksm_reference_number' => 'required|unique:directrecruitment_application_approval',
                'received_date' => 'required|date|date_format:Y-m-d',
                'valid_until' => 'required|date|date_format:Y-m-d'
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
                'ksm_reference_number' => 'required|unique:directrecruitment_application_approval,ksm_reference_number,'.$param['id'],
                'received_date' => 'required|date|date_format:Y-m-d',
                'valid_until' => 'required|date|date_format:Y-m-d'
            ];
    }
    /**
     * @param $request
     * @return mixed
     */
    public function list($request): mixed
    {
        return $this->directRecruitmentApplicationApproval->join('directrecruitment_applications', function ($join) use($request) {
            $join->on('directrecruitment_applications.id', '=', 'directrecruitment_application_approval.application_id')
            ->whereIn('directrecruitment_applications.company_id', $request['company_id']);
        })->where('directrecruitment_application_approval.application_id', $request['application_id'])
        ->select('directrecruitment_application_approval.id', 'directrecruitment_application_approval.application_id', 'directrecruitment_application_approval.item_name', 'directrecruitment_application_approval.ksm_reference_number',  'directrecruitment_application_approval.received_date',  'directrecruitment_application_approval.valid_until', 'directrecruitment_application_approval.updated_at')
        ->orderBy('directrecruitment_application_approval.id', 'desc')
        ->paginate(Config::get('services.paginate_row'));
    }
    /**
     * @param $request
     * @return mixed
     */
    public function show($request): mixed
    {
        return $this->directRecruitmentApplicationApproval->with(['approvalAttachment' => function ($query) {
            $query->orderBy('created_at', 'desc');
        }])->join('directrecruitment_applications', function ($join) use($request) {
            $join->on('directrecruitment_applications.id', '=', 'directrecruitment_application_approval.application_id')
            ->whereIn('directrecruitment_applications.company_id', $request['company_id']);
        })->where('directrecruitment_application_approval.id', $request['id'])
        ->first('directrecruitment_application_approval.*');
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
        $applicationCheck = $this->directrecruitmentApplications->find($request['application_id']);
        if($applicationCheck->company_id != $request['company_id']) {
            return [
                'InvalidUser' => true
            ];
        }
        $approvalDetails = $this->directRecruitmentApplicationApproval->create([
            'application_id' => $request['application_id'] ?? 0,
            'item_name' => 'Approval Letter',
            'ksm_reference_number' => $request['ksm_reference_number'] ?? '',
            'received_date' => $request['received_date'] ?? '',
            'valid_until' => $request['valid_until'] ?? '',
            'created_by' =>  $request['created_by'] ?? 0,
            'modified_by' =>  $request['modified_by'] ?? 0
        ]);
        $approvalId = $approvalDetails->id;

        if (request()->hasFile('levy_payment_receipt')){
            foreach($request->file('levy_payment_receipt') as $file){
                $fileName = $file->getClientOriginalName();
                $filePath = '/directRecruitment/approval/levyPaymentReceipt/' . $fileName; 
                $linode = $this->storage::disk('linode');
                $linode->put($filePath, file_get_contents($file));
                $fileUrl = $this->storage::disk('linode')->url($filePath);
                $this->approvalAttachments::create([
                        "file_id" => $approvalId,
                        "file_name" => $fileName,
                        "file_type" => 'Levy Payment Receipt',
                        "file_url" =>  $fileUrl         
                    ]);  
            }
        } 
        
        if (request()->hasFile('approval_letter')){
            foreach($request->file('approval_letter') as $file){
                $fileName = $file->getClientOriginalName();
                $filePath = '/directRecruitment/approval/approvalLetter/' . $fileName; 
                $linode = $this->storage::disk('linode');
                $linode->put($filePath, file_get_contents($file));
                $fileUrl = $this->storage::disk('linode')->url($filePath);
                $this->approvalAttachments::create([
                        "file_id" => $approvalId,
                        "file_name" => $fileName,
                        "file_type" => 'Approval letter',
                        "file_url" =>  $fileUrl         
                    ]);  
            }
        }

        $request['ksm_reference_number'] = $request['ksm_reference_number'] ?? '';
        $request['action'] = Config::get('services.APPLICATION_SUMMARY_ACTION')[6];
        $request['status'] = 'Submitted';
        $this->applicationSummaryServices->ksmUpdateStatus($request);

        $ksmCount = $this->fwcms->where('application_id', $request['application_id'])
                    ->where('status', '!=', 'Rejected')            
                    ->count('ksm_reference_number');
        $approvalCount = $this->directRecruitmentApplicationApproval->where('application_id', $request['application_id'])->count('ksm_reference_number');
        if($ksmCount == $approvalCount) {
            $applicationDetails = $this->directrecruitmentApplications->findOrFail($request['application_id']);
            $applicationDetails->status = Config::get('services.APPROVAL_COMPLETED');
            $applicationDetails->approval_flag = 1;
            $applicationDetails->save();

            $request['action'] = Config::get('services.APPLICATION_SUMMARY_ACTION')[6];
            $request['status'] = 'Completed';
            $this->applicationSummaryServices->updateStatus($request);

            $serviceDetails = $this->crmProspectService->findOrFail($applicationDetails->service_id);
            $serviceDetails->status = 0;
            $serviceDetails->save();
        } else { 
            $applicationDetails = $this->directrecruitmentApplications->findOrFail($request['application_id']);
            $applicationDetails->approval_flag = 1;
            $applicationDetails->save();
        }

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
        $approvalDetails = $this->directRecruitmentApplicationApproval->findOrFail($request['id']);
        $applicationCheck = $this->directrecruitmentApplications->find($request['application_id']);
        if($applicationCheck->company_id != $request['company_id']) {
            return [
                'InvalidUser' => true
            ];
        } else if($request['application_id'] != $approvalDetails->application_id) {
            return [
                'InvalidUser' => true
            ];
        }

        $approvalDetails->application_id       = $request['application_id'] ?? $approvalDetails->application_id;
        $approvalDetails->ksm_reference_number = $request['ksm_reference_number'] ?? $approvalDetails->ksm_reference_number;
        $approvalDetails->received_date        = $request['received_date'] ?? $approvalDetails->received_date;
        $approvalDetails->valid_until          = $request['valid_until'] ?? $approvalDetails->valid_until;
        $approvalDetails->modified_by          = $request['modified_by'] ?? $approvalDetails->modified_by;
        $approvalDetails->save();
        
        if (request()->hasFile('levy_payment_receipt')){
            $this->approvalAttachments->where([['file_id', $request['id']], ['file_type', 'Levy Payment Receipt']])->delete();
            foreach($request->file('levy_payment_receipt') as $file){
                $fileName = $file->getClientOriginalName();
                $filePath = '/directRecruitment/approval/levyPaymentReceipt/' . $fileName; 
                $linode = $this->storage::disk('linode');
                $linode->put($filePath, file_get_contents($file));
                $fileUrl = $this->storage::disk('linode')->url($filePath);
                $this->approvalAttachments::create([
                        "file_id" => $request['id'],
                        "file_name" => $fileName,
                        "file_type" => 'Levy Payment Receipt',
                        "file_url" =>  $fileUrl         
                    ]);  
            }
        } 
        
        if (request()->hasFile('approval_letter')){
            $this->approvalAttachments->where([['file_id', $request['id']], ['file_type', 'Approval letter']])->delete();
            foreach($request->file('approval_letter') as $file){
                $fileName = $file->getClientOriginalName();
                $filePath = '/directRecruitment/approval/approvalLetter/' . $fileName; 
                $linode = $this->storage::disk('linode');
                $linode->put($filePath, file_get_contents($file));
                $fileUrl = $this->storage::disk('linode')->url($filePath);
                $this->approvalAttachments::create([
                        "file_id" => $request['id'],
                        "file_name" => $fileName,
                        "file_type" => 'Approval letter',
                        "file_url" =>  $fileUrl         
                    ]);  
            }
        }

        $ksmCount = $this->fwcms->where('application_id', $request['application_id'])
                    ->where('status', '!=', 'Rejected') 
                    ->count('ksm_reference_number');
        $approvalCount = $this->directRecruitmentApplicationApproval->where('application_id', $request['application_id'])->count('ksm_reference_number');
        if($ksmCount == $approvalCount) {
            $applicationDetails = $this->directrecruitmentApplications->findOrFail($request['application_id']);
            $applicationDetails->status = Config::get('services.APPROVAL_COMPLETED');
            $applicationDetails->approval_flag = 1;
            $applicationDetails->save();

            $request['action'] = Config::get('services.APPLICATION_SUMMARY_ACTION')[6];
            $request['status'] = 'Completed';
            $this->applicationSummaryServices->updateStatus($request);

            $serviceDetails = $this->crmProspectService->findOrFail($applicationDetails->service_id);
            $serviceDetails->status = 0;
            $serviceDetails->save();
        } else { 
            $applicationDetails = $this->directrecruitmentApplications->findOrFail($request['application_id']);
            $applicationDetails->approval_flag = 1;
            $applicationDetails->save();
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
        $data = $this->approvalAttachments->with(['directRecruitmentApplicationApproval' => function ($query) {
            $query->select('id', 'application_id');
        }])->find($request['id']); 
        
        if(is_null($data)){
            return [
                "isDeleted" => false,
                "message" => "Data not found"
            ];
        }

        $applicationCheck = $this->directrecruitmentApplications->find($data['directRecruitmentApplicationApproval']['application_id']);
        if($applicationCheck->company_id != $request['company_id']) {
            return [
                'InvalidUser' => true
            ];
        }

        return [
            "isDeleted" => $data->delete(),
            "message" => "Deleted Successfully"
        ];
    }

}