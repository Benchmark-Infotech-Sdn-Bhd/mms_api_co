<?php

namespace App\Services;

use App\Models\DocumentChecklistAttachments;
use App\Models\DocumentChecklist;
use App\Services\ValidationServices;
use Illuminate\Support\Facades\Config;
use App\Services\DocumentChecklistServices;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DocumentChecklistAttachmentsServices
{
    private DocumentChecklistAttachments $documentChecklistAttachments;
    private ValidationServices $validationServices;
    private DocumentChecklistServices $documentChecklistServices;
    private DocumentChecklist $documentChecklist;
    private Storage $storage;
    /**
     * DocumentChecklistAttachmentsServices constructor.
     * @param DocumentChecklistAttachments $documentChecklistAttachments
     * @param DocumentChecklist $documentChecklist
     * @param ValidationServices $validationServices
     * @param DocumentChecklistServices $documentChecklistServices
     * @param Storage $storage
     */
    public function __construct(DocumentChecklistAttachments $documentChecklistAttachments,ValidationServices $validationServices,
    DocumentChecklistServices $documentChecklistServices,DocumentChecklist $documentChecklist,
    Storage $storage)
    {
        $this->documentChecklistAttachments = $documentChecklistAttachments;
        $this->validationServices = $validationServices;
        $this->documentChecklistServices = $documentChecklistServices;
        $this->documentChecklist = $documentChecklist;
        $this->storage = $storage;
    }

    /**
     * @param $request
     * @return mixed
     */
    public function create($request) : mixed
    {
        if(!($this->validationServices->validate($request,$this->documentChecklistAttachments->rules))){
            return [
                'validate' => $this->validationServices->errors()
            ];
        }
        $documentChecklist = $this->documentChecklist->find($request['document_checklist_id']);
        if(is_null($documentChecklist)){
            return [
                "isCreated" => false,
                "message"=> "Data not found"
            ];
        }
        Log::error("request".$request);
        if ($request->hasFile('attachment')){
            foreach($request->file('attachment') as $file){
                Log::error("file".$file.file_get_contents($file));               
                $filePath = '/application/checklist/attachments/' . $documentChecklist['document_title']; 
                $linode = $this->storage::disk('linode');
                $linode->put($filePath, file_get_contents($file));
                $fileUrl = $this->storage::disk('linode')->url($filePath);
                        // $attachment = $this->documentChecklistAttachments->create([
                        //     'document_checklist_id' => (int)$request['document_checklist_id'] ?? 0,
                        //     'application_id' => (int)$request['application_id'] ?? 0,
                        //     'file_type' => 'application-checklist',
                        //     'file_url' => $fileUrl ?? '',
                        //     'created_by'    => $request['created_by'] ?? 0,
                        //     'modified_by'   => $request['created_by'] ?? 0
                        // ]);
            }
        }
        $count = $this->documentChecklistAttachments->whereNull('deleted_at')
        ->where(function ($query) use ($request) {
            if (isset($request['document_checklist_id'])) {
                $query->where('document_checklist_attachments.document_checklist_id',$request['document_checklist_id']);
            }
            if (isset($request['application_id'])) {
                $query->where('document_checklist_attachments.application_id',$request['application_id']);
            }
        })->count('id');
        if($count == 1){
        // $result =  $this->sectorsServices->updateChecklistStatus([ 'id' => $request['sector_id'], 'checklist_status' => 'Done' ]);
        }
        return $attachment;
    }
}
