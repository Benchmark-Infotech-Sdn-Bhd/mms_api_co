<?php

namespace App\Services;

use App\Models\DirectRecruitmentApplicationApproval;
use App\Models\ApprovalAttachments;
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
     * DirectRecruitmentApplicationApprovalServices Constructor
     * @param DirectRecruitmentApplicationApproval $directRecruitmentApplicationApproval
     * @param ApprovalAttachments $approvalAttachments
     * @param Storage $storage
     */
    public function __construct(DirectRecruitmentApplicationApproval $directRecruitmentApplicationApproval, ApprovalAttachments $approvalAttachments, 
    Storage $storage)
    {
        $this->directRecruitmentApplicationApproval = $directRecruitmentApplicationApproval;
        $this->approvalAttachments = $approvalAttachments;
        $this->storage = $storage;
    }
    /**
     * @return array
     */
    public function createValidation(): array
    {
        return
            [
                'application_id' => 'required',
                'ksm_reference_number' => 'required|regex:/^[a-zA-Z0-9\/]*$/|max:21',
                'received_date' => 'required|date|date_format:Y-m-d',
                'valid_until' => 'required|date|date_format:Y-m-d'
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
                'ksm_reference_number' => 'required|regex:/^[a-zA-Z0-9\/]*$/|max:21',
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
        return $this->directRecruitmentApplicationApproval->where('application_id', $request['application_id'])
        ->select('id', 'application_id', 'ksm_reference_number',  'received_date',  'valid_until', 'updated_at')
        ->orderBy('id', 'desc')
        ->paginate(Config::get('services.paginate_row'));
    }
    /**
     * @param $request
     * @return mixed
     */
    public function show($request): mixed
    {
        return $this->directRecruitmentApplicationApproval::with(['approvalAttachment' => function ($query) {
            $query->orderBy('created_at', 'desc');
        }])->find($request['id']);
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

        return true;
    }
    /**
     * @param $request
     * @return bool|array
     */
    public function update($request): bool|array
    {
        $validator = Validator::make($request->toArray(), $this->updateValidation());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }
        $approvalDetails = $this->directRecruitmentApplicationApproval->findOrFail($request['id']);
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

        return true;
    }

    /**
     *
     * @param $request
     * @return mixed
     */    
    public function deleteAttachment($request): mixed
    {   
        $data = $this->approvalAttachments::find($request['id']); 
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

}