<?php

namespace App\Services;

use App\Models\DocumentChecklistAttachments;
use App\Models\DocumentChecklist;
use App\Services\ValidationServices;
use Illuminate\Support\Facades\Config;
use App\Services\DirectRecruitmentServices;
use App\Services\DirectRecruitmentApplicationChecklistServices;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\Facades\JWTAuth;
use Carbon\Carbon;

class DocumentChecklistAttachmentsServices
{
    public const MESSAGE_DATA_NOT_FOUND = "Data not found";
    public const MESSAGE_DOCUMENT_NOT_FOUND = "Document not found";
    public const MESSAGE_DOCUMENT_UPLOADED_SUCCESSFULLY = "Document uploaded Successfully";
    public const MESSAGE_DELETED_SUCCESSFULLY = "Deleted Successfully";
    public const STATUS_PENDING = "Pending";
    public const STATUS_PROPOSAL_SUBMITTED = "Proposal Submitted";
    public const STATUS_COMPLETED = "Completed";
    public const STATUS_CHECKLIST_COMPLETED = "Checklist Completed";
    public const FILE_TYPE_CHECKLIST = "checklist";
    public const DEFAULT_INTEGER_VALUE_ZERO = 0;
    public const DEFAULT_INTEGER_VALUE_ONE = 1;

    /**
     * @var DocumentChecklistAttachments
     */
    private DocumentChecklistAttachments $documentChecklistAttachments;

    /**
     * @var ValidationServices
     */
    private ValidationServices $validationServices;

    /**
     * @var DocumentChecklist
     */
    private DocumentChecklist $documentChecklist;

    /**
     * @var Storage
     */
    private Storage $storage;

    /**
     * @var DirectRecruitmentApplicationChecklistServices
     */
    private DirectRecruitmentApplicationChecklistServices $directRecruitmentApplicationChecklistServices;

    /**
     * @var DirectRecruitmentServices
     */
    private DirectRecruitmentServices $directRecruitmentServices;

    /**
     * @var ApplicationSummaryServices
     */
    private ApplicationSummaryServices $applicationSummaryServices;

    /**
     * Constructor method.
     * 
     * @param DocumentChecklistAttachments $documentChecklistAttachments Instance of the DocumentChecklistAttachments class.
     * @param DocumentChecklist $documentChecklist Instance of the DocumentChecklist class.
     * @param ValidationServices $validationServices Instance of the ValidationServices class.
     * @param DirectRecruitmentApplicationChecklistServices $directRecruitmentApplicationChecklistServices Instance of the DirectRecruitmentApplicationChecklistServices class.
     * @param Storage $storage Instance of the Storage class.
     * @param DirectRecruitmentServices $directRecruitmentServices Instance of the DirectRecruitmentServices class.
     * @param ApplicationSummaryServices $applicationSummaryServices Instance of the ApplicationSummaryServices class.
     */
    public function __construct(
        DocumentChecklistAttachments                  $documentChecklistAttachments,
        ValidationServices                            $validationServices,
        DocumentChecklist                             $documentChecklist,
        Storage                                       $storage,
        DirectRecruitmentApplicationChecklistServices $directRecruitmentApplicationChecklistServices,
        DirectRecruitmentServices                     $directRecruitmentServices,
        ApplicationSummaryServices                    $applicationSummaryServices
    )
    {
        $this->documentChecklistAttachments = $documentChecklistAttachments;
        $this->validationServices = $validationServices;
        $this->documentChecklist = $documentChecklist;
        $this->storage = $storage;
        $this->directRecruitmentApplicationChecklistServices = $directRecruitmentApplicationChecklistServices;
        $this->directRecruitmentServices = $directRecruitmentServices;
        $this->applicationSummaryServices = $applicationSummaryServices;
    }

    /**
     * @param $request
     * @return mixed
     */
    public function create($request) : mixed
    {
        $user = JWTAuth::parseToken()->authenticate();
        $request['created_by'] = $user['id'];
        $request['modified_by'] = $user['id'];

        $validationResult = $this->createValidateRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }
        
        $documentChecklist = $this->showDocumentChecklist($request);
        $directRecruitmentApplicationChecklist = $this->showDirectRecruitmentApplicationChecklistServices($request);
        if(is_null($documentChecklist)){
            return [
                "isUploaded" => false,
                "message"=> self::MESSAGE_DATA_NOT_FOUND
            ];
        }

        if (request()->hasFile('attachment')){
            $this->uploadDocumentChecklistAttachments($directRecruitmentApplicationChecklist, $request);
        }
        else
        {
            return [
                "isUploaded" => false,
                "message" => self::MESSAGE_DOCUMENT_NOT_FOUND
            ];
        }
        
        $this->createDirectRecruitmentApplicationChecklistUpdateStatus($directRecruitmentApplicationChecklist, $request);

        return [
            "isUploaded" => true,
            "message" => self::MESSAGE_DOCUMENT_UPLOADED_SUCCESSFULLY
        ];
    }
    /**
     * @param $request
     * @return mixed
     */
    public function delete($request) : mixed
    {
        $validationResult = $this->deleteValidateRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }

        $user = JWTAuth::parseToken()->authenticate();
        $request['modified_by'] = $user['id'];
        $directrecruitmentApplicationAttachment = $this->showDocumentChecklistAttachments($request);
        if(is_null($directrecruitmentApplicationAttachment)){
            return [
                "isDeleted" => false,
                "message" => self::MESSAGE_DATA_NOT_FOUND
            ];
        }

        $directRecruitmentApplicationChecklist = $this->showDirectRecruitmentApplicationChecklistServices(["application_id" =>  $directrecruitmentApplicationAttachment['application_id']]);
        $res = [
            "isDeleted" => $directrecruitmentApplicationAttachment->delete(),
            "message" => self::MESSAGE_DELETED_SUCCESSFULLY
        ];

        if($res['isDeleted']){
            $this->deleteDirectRecruitmentApplicationChecklistUpdateStatus($directrecruitmentApplicationAttachment, $directRecruitmentApplicationChecklist, $request);
        }

        return $res;
    }
    /**
     * @param $request
     * @return mixed
     */
    public function list($request) : mixed
    {
        $validationResult = $this->listValidateRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }

        return $this->documentChecklist->where('sector_id',$request['sector_id'])
        ->with(["documentChecklistAttachments" => function($attachment) use ($request){
            $attachment->where('application_id',$request['application_id']);
        }])->orderBy('document_checklist.created_at','DESC')
        ->paginate(Config::get('services.paginate_row'));
    }

    /**
     * Validate the given request data.
     *
     * @param array $request The request data to be validated.
     * @return array|bool Returns an array with 'error' as key and validation error messages as value if validation fails. | Returns true if validation passes.
     */
    private function createValidateRequest($request): array|bool
    {
        if(!($this->validationServices->validate($request,$this->documentChecklistAttachments->rules))){
            return [
                'validate' => $this->validationServices->errors()
            ];
        }

        return true;
    }

    private function showDocumentChecklist($request)
    {
        return $this->documentChecklist->find($request['document_checklist_id']);
    }

    private function showDirectRecruitmentApplicationChecklistServices($request)
    {
        return $this->directRecruitmentApplicationChecklistServices->showBasedOnApplication(["application_id" => $request['application_id']]);
    }

    private function getDocumentChecklistAttachmentsCount($request)
    {
        return $this->documentChecklistAttachments->whereNull('deleted_at')
            ->where(function ($query) use ($request) {
                if (isset($request['application_id'])) {
                    $query->where('document_checklist_attachments.application_id',$request['application_id']);
                }
            })
            ->count('id');
    }

    private function updateDirectRecruitmentStatus($request, $status)
    {
        $this->directRecruitmentServices->updateStatus(['id' => $request['application_id'] , 'status' => $status]);
    }

    private function updateApplicationSummaryStatus($request)
    {
        $request['action'] = Config::get('services.APPLICATION_SUMMARY_ACTION')[2];
        $request['status'] = self::STATUS_COMPLETED;
        $this->applicationSummaryServices->updateStatus($request);
    }

    private function uploadDocumentChecklistAttachments($directRecruitmentApplicationChecklist, $request)
    {
        foreach($request->file('attachment') as $file){
            $fileName = $file->getClientOriginalName();
            $filePath = '/directRecruitment/application/checklist/' . $fileName; 
            $linode = $this->storage::disk('linode');
            $linode->put($filePath, file_get_contents($file));
            $fileUrl = $linode->disk('linode')->url($filePath);
            $this->documentChecklistAttachments->create([
                "document_checklist_id" => $request['document_checklist_id'],
                "application_id" => $request['application_id'],
                "application_checklist_id" => $directRecruitmentApplicationChecklist['id'] ?? self::DEFAULT_INTEGER_VALUE_ZERO,
                "file_type" => self::FILE_TYPE_CHECKLIST,
                "file_url" =>  $fileUrl ,
                "created_by"    => $request['created_by'] ?? self::DEFAULT_INTEGER_VALUE_ZERO,
                "modified_by"   => $request['created_by'] ?? self::DEFAULT_INTEGER_VALUE_ZERO    
            ]);
        }
    }

    private function createDirectRecruitmentApplicationChecklistUpdateStatus($directRecruitmentApplicationChecklist, $request)
    {
        $count = $this->getDocumentChecklistAttachmentsCount($request);
        $directRecruitmentApplicationChecklist->modified_on = Carbon::now();
        if($count == self::DEFAULT_INTEGER_VALUE_ONE){
            $res = $this->updateDirectRecruitmentStatus($request, self::STATUS_CHECKLIST_COMPLETED);
            $directRecruitmentApplicationChecklist->application_checklist_status = self::STATUS_COMPLETED;
            $directRecruitmentApplicationChecklist->modified_by = $request['modified_by'] ?? $directRecruitmentApplicationChecklist['modified_by'];
            $directRecruitmentApplicationChecklist->submitted_on = Carbon::now();
            
            $this->updateApplicationSummaryStatus($request);
        }
        $directRecruitmentApplicationChecklist->save();
    }

    /**
     * Validate the given request data.
     *
     * @param array $request The request data to be validated.
     * @return array|bool Returns an array with 'error' as key and validation error messages as value if validation fails. | Returns true if validation passes.
     */
    private function deleteValidateRequest($request): array|bool
    {
        if(!($this->validationServices->validate($request,['id' => 'required']))){
            return [
                'validate' => $this->validationServices->errors()
            ];
        }

        return true;
    }

    private function showDocumentChecklistAttachments($request)
    {
        return $this->documentChecklistAttachments->find($request['id']);
    }

    private function deleteApplicationSummaryStatus($directrecruitmentApplicationAttachment, $request)
    {
        $request['application_id'] = $directrecruitmentApplicationAttachment['application_id'];
        $request['action'] = Config::get('services.APPLICATION_SUMMARY_ACTION')[2];
        $this->applicationSummaryServices->deleteStatus($request);
    }

    private function getDirectrecruitmentApplicationAttachmentCount($directrecruitmentApplicationAttachment)
    {
        return $this->documentChecklistAttachments->whereNull('deleted_at')
            ->where(function ($query) use ($directrecruitmentApplicationAttachment) {
                if (isset($directrecruitmentApplicationAttachment['application_id'])) {
                    $query->where('document_checklist_attachments.application_id',$directrecruitmentApplicationAttachment['application_id']);
                }
            })
            ->count('id');
    }

    /**
     * Validate the given request data.
     *
     * @param array $request The request data to be validated.
     * @return array|bool Returns an array with 'error' as key and validation error messages as value if validation fails. | Returns true if validation passes.
     */
    private function listValidateRequest($request): array|bool
    {
        if(!($this->validationServices->validate($request,['application_id' => 'required', 'sector_id' => 'required']))){
            return [
                'validate' => $this->validationServices->errors()
            ];
        }

        return true;
    }

    private function deleteDirectRecruitmentApplicationChecklistUpdateStatus($directrecruitmentApplicationAttachment, $directRecruitmentApplicationChecklist, $request)
    {
        $count = $this->getDirectrecruitmentApplicationAttachmentCount($directrecruitmentApplicationAttachment);
        $directRecruitmentApplicationChecklist->modified_on = Carbon::now();
        if($count == self::DEFAULT_INTEGER_VALUE_ZERO){
            $resUpdate = $this->updateDirectRecruitmentStatus(['application_id' => $directrecruitmentApplicationAttachment['application_id']] , self::STATUS_PROPOSAL_SUBMITTED);
            $directRecruitmentApplicationChecklist->application_checklist_status = self::STATUS_PENDING;
            $directRecruitmentApplicationChecklist->modified_by = $request['modified_by'] ?? $directRecruitmentApplicationChecklist['modified_by'];
            
            $this->deleteApplicationSummaryStatus($directrecruitmentApplicationAttachment, $request);
        }
        $directRecruitmentApplicationChecklist->save();
    }
}
