<?php

namespace App\Services;

use App\Models\DocumentChecklistAttachments;
use App\Models\DocumentChecklist;
use App\Services\ValidationServices;
use Illuminate\Support\Facades\Config;
use App\Services\DocumentChecklistServices;
use App\Services\DirectRecruitmentServices;
use App\Services\DirectRecruitmentApplicationChecklistServices;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\Facades\JWTAuth;

class DocumentChecklistAttachmentsServices
{
    private DocumentChecklistAttachments $documentChecklistAttachments;
    private ValidationServices $validationServices;
    private DocumentChecklistServices $documentChecklistServices;
    private DocumentChecklist $documentChecklist;
    private Storage $storage;
    private DirectRecruitmentApplicationChecklistServices $directRecruitmentApplicationChecklistServices;
    private DirectRecruitmentServices $directRecruitmentServices;
    /**
     * DocumentChecklistAttachmentsServices constructor.
     * @param DocumentChecklistAttachments $documentChecklistAttachments
     * @param DocumentChecklist $documentChecklist
     * @param ValidationServices $validationServices
     * @param DocumentChecklistServices $documentChecklistServices
     * @param DirectRecruitmentApplicationChecklistServices $directRecruitmentApplicationChecklistServices
     * @param Storage $storage
     * @param DirectRecruitmentServices $directRecruitmentServices
     */
    public function __construct(DocumentChecklistAttachments $documentChecklistAttachments,ValidationServices $validationServices,
    DocumentChecklistServices $documentChecklistServices,DocumentChecklist $documentChecklist,
    Storage $storage,DirectRecruitmentApplicationChecklistServices $directRecruitmentApplicationChecklistServices,
    DirectRecruitmentServices $directRecruitmentServices)
    {
        $this->documentChecklistAttachments = $documentChecklistAttachments;
        $this->validationServices = $validationServices;
        $this->documentChecklistServices = $documentChecklistServices;
        $this->documentChecklist = $documentChecklist;
        $this->storage = $storage;
        $this->directRecruitmentApplicationChecklistServices = $directRecruitmentApplicationChecklistServices;
        $this->directRecruitmentServices = $directRecruitmentServices;
    }

    /**
     * @param $request
     * @return mixed
     */
    public function create($request) : mixed
    {
        $params = $request->all();
        $user = JWTAuth::parseToken()->authenticate();
        $params['created_by'] = $user['id'];
        if(!($this->validationServices->validate($params,$this->documentChecklistAttachments->rules))){
            return [
                'validate' => $this->validationServices->errors()
            ];
        }
        $documentChecklist = $this->documentChecklist->find($params['document_checklist_id']);
        $directRecruitmentApplicationChecklist = $this->directRecruitmentApplicationChecklistServices->showBasedOnApplication(["application_id" => $params['application_id']]);
        if(is_null($documentChecklist)){
            return [
                "isUploaded" => false,
                "message"=> "Data not found"
            ];
        }
        if (request()->hasFile('attachment')){
            foreach($request->file('attachment') as $file){
                $fileName = $file->getClientOriginalName();
                $filePath = '/directRecruitment/application/checklist/' . $fileName; 
                $linode = $this->storage::disk('linode');
                $linode->put($filePath, file_get_contents($file));
                $fileUrl = $this->storage::disk('linode')->url($filePath);
                $this->documentChecklistAttachments->create([
                            "document_checklist_id" => $params['document_checklist_id'],
                            "application_id" => $params['application_id'],
                            "application_checklist_id" => $directRecruitmentApplicationChecklist['id'] ?? 0,
                            "file_type" => 'checklist',
                            "file_url" =>  $fileUrl ,
                            "created_by"    => $params['created_by'] ?? 0,
                            "modified_by"   => $params['created_by'] ?? 0    
                        ]);  
            }
        }else{
            return [
                "isUploaded" => false,
                "message" => "Document not found"
            ];
        }
        $count = $this->documentChecklistAttachments->whereNull('deleted_at')
        ->where(function ($query) use ($params) {
            if (isset($params['application_id'])) {
                $query->where('document_checklist_attachments.application_id',$params['application_id']);
            }
        })->count('id');
        if($count == 1){
            $result =  $this->directRecruitmentApplicationChecklistServices->updateStatusBasedOnApplication([ 'application_id' => $params['application_id'], 'status' => 'Completed', 'user_id' => $user['id']]);
            $res = $this->directRecruitmentServices->updateStatus(['id' => $params['application_id'] , 'status' => 'Checklist Completed']);
        }
        return [
            "isUploaded" => true,
            "message" => "Document uploaded Successfully"
        ];
    }
    /**
     * @param $request
     * @return mixed
     */
    public function delete($request) : mixed
    {
        if(!($this->validationServices->validate($request,['id' => 'required']))){
            return [
                'validate' => $this->validationServices->errors()
            ];
        }
        $user = JWTAuth::parseToken()->authenticate();
        $directrecruitmentApplicationAttachment = $this->documentChecklistAttachments->find($request['id']);
        if(is_null($directrecruitmentApplicationAttachment)){
            return [
                "isDeleted" => false,
                "message" => "Data not found"
            ];
        }
        $res = [
            "isDeleted" => $directrecruitmentApplicationAttachment->delete(),
            "message" => "Deleted Successfully"
        ];
        if($res['isDeleted']){
            $count = $this->documentChecklistAttachments->whereNull('deleted_at')
            ->where(function ($query) use ($directrecruitmentApplicationAttachment) {
                if (isset($directrecruitmentApplicationAttachment['application_id'])) {
                    $query->where('document_checklist_attachments.application_id',$directrecruitmentApplicationAttachment['application_id']);
                }
            })->count('id');
            if($count == 0){
                $result =  $this->directRecruitmentApplicationChecklistServices->updateStatusBasedOnApplication([ 'application_id' => $directrecruitmentApplicationAttachment['application_id'], 'status' => 'Pending', 'user_id' => $user['id'] ]);
                $resUpdate = $this->directRecruitmentServices->updateStatus(['id' => $directrecruitmentApplicationAttachment['application_id'] , 'status' => 'Checklist Pending']);
            }
        }
        return $res;
    }
    /**
     * @param $request
     * @return mixed
     */
    public function list($request) : mixed
    {
        if(!($this->validationServices->validate($request,['application_id' => 'required', 'sector_id' => 'required']))){
            return [
                'validate' => $this->validationServices->errors()
            ];
        }
        return $this->documentChecklist->where('sector_id',$request['sector_id'])
        ->with(["documentChecklistAttachments" => function($attachment) use ($request){
            $attachment->where('application_id',$request['application_id']);
        }])->orderBy('document_checklist.created_at','DESC')
        ->paginate(Config::get('services.paginate_row'));
    }
}
