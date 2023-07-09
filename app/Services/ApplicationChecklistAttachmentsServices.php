<?php

namespace App\Services;

use App\Models\ApplicationChecklistAttachments;
use App\Models\DirectRecruitmentApplicationChecklist;
use App\Services\ValidationServices;
use Illuminate\Support\Facades\Config;
use App\Services\DirectRecruitmentServices;
use App\Services\DirectRecruitmentApplicationChecklistServices;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\Facades\JWTAuth;
use Carbon\Carbon;

class ApplicationChecklistAttachmentsServices
{
    private ApplicationChecklistAttachments $applicationtChecklistAttachments;
    private DirectRecruitmentApplicationChecklist $directRecruitmentApplicationChecklist;
    private ValidationServices $validationServices;
    private Storage $storage;
    private DirectRecruitmentApplicationChecklistServices $directRecruitmentApplicationChecklistServices;
    private DirectRecruitmentServices $directRecruitmentServices;
    private ApplicationSummaryServices $applicationSummaryServices;
    /**
     * ApplicationChecklistAttachmentsServices constructor.
     * @param ApplicationChecklistAttachments $applicationChecklistAttachments
     * @param DirectRecruitmentApplicationChecklist $directRecruitmentApplicationChecklist
     * @param ValidationServices $validationServices
     * @param DirectRecruitmentApplicationChecklistServices $directRecruitmentApplicationChecklistServices
     * @param Storage $storage
     * @param DirectRecruitmentServices $directRecruitmentServices
     * @param ApplicationSummaryServices $applicationSummaryServices;
     */
    public function __construct(ApplicationChecklistAttachments $applicationChecklistAttachments, DirectRecruitmentApplicationChecklist $directRecruitmentApplicationChecklist, ValidationServices $validationServices,
    Storage $storage,DirectRecruitmentApplicationChecklistServices $directRecruitmentApplicationChecklistServices,
    DirectRecruitmentServices $directRecruitmentServices, ApplicationSummaryServices $applicationSummaryServices)
    {
        $this->applicationChecklistAttachments = $applicationChecklistAttachments;
        $this->directRecruitmentApplicationChecklist = $directRecruitmentApplicationChecklist;
        $this->validationServices = $validationServices;
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
        $params = $request->all();
        $user = JWTAuth::parseToken()->authenticate();
        $params['created_by'] = $user['id'];
        if(!($this->validationServices->validate($params,$this->applicationChecklistAttachments->rules))){
            return [
                'validate' => $this->validationServices->errors()
            ];
        }
        $directRecruitmentApplicationChecklist = $this->directRecruitmentApplicationChecklistServices->showBasedOnApplication(["application_id" => $params['application_id']]);
        if (request()->hasFile('attachment')){
            foreach($request->file('attachment') as $file){
                $fileName = $file->getClientOriginalName();
                $filePath = '/directRecruitment/application/checklist/' . $fileName; 
                $linode = $this->storage::disk('linode');
                $linode->put($filePath, file_get_contents($file));
                $fileUrl = $this->storage::disk('linode')->url($filePath);
                $this->applicationChecklistAttachments->create([
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
        $count = $this->applicationChecklistAttachments->whereNull('deleted_at')
        ->where(function ($query) use ($params) {
            if (isset($params['application_id'])) {
                $query->where('application_id',$params['application_id']);
            }
        })->count('id');
        $directRecruitmentApplicationChecklist->modified_on = Carbon::now();
        if($count == 1){
            $res = $this->directRecruitmentServices->updateStatus(['id' => $params['application_id'] , 'status' => Config::get('services.CHECKLIST_COMPLETED')]);
            $directRecruitmentApplicationChecklist->application_checklist_status = 'Completed';
            $directRecruitmentApplicationChecklist->modified_by = $user['id'] ?? $directRecruitmentApplicationChecklist['modified_by'];
            $directRecruitmentApplicationChecklist->submitted_on = Carbon::now();

            $params['action'] = Config::get('services.APPLICATION_SUMMARY_ACTION')[2];
            $params['status'] = 'Completed';
            $this->applicationSummaryServices->updateStatus($params);
        }
        $directRecruitmentApplicationChecklist->save();
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
        $directrecruitmentApplicationAttachment = $this->applicationChecklistAttachments->find($request['id']);
        if(is_null($directrecruitmentApplicationAttachment)){
            return [
                "isDeleted" => false,
                "message" => "Data not found"
            ];
        }
        $directRecruitmentApplicationChecklist = $this->directRecruitmentApplicationChecklistServices->showBasedOnApplication(["application_id" =>  $directrecruitmentApplicationAttachment['application_id']]);
       
        $deleteApplicationChecklistAttachment = $directrecruitmentApplicationAttachment->delete();
        $res = [
            "isDeleted" => true,
            "message" => "Deleted Successfully"
        ];

        if($deleteApplicationChecklistAttachment){

            $count = $this->applicationChecklistAttachments->whereNull('deleted_at')
            ->where(function ($query) use ($directrecruitmentApplicationAttachment) {
                if (isset($directrecruitmentApplicationAttachment['application_id'])) {
                    $query->where('application_checklist_attachments.application_id',$directrecruitmentApplicationAttachment['application_id']);
                }
            })->count('id');
            $directRecruitmentApplicationChecklist->modified_on = Carbon::now();
            if($count == 0){
                $resUpdate = $this->directRecruitmentServices->updateStatus(['id' => $directrecruitmentApplicationAttachment['application_id'] , 'status' => Config::get('services.PROPOSAL_SUBMITTED')]);
                $directRecruitmentApplicationChecklist->application_checklist_status = 'Pending';
                $directRecruitmentApplicationChecklist->modified_by = $user['id'] ?? $directRecruitmentApplicationChecklist['modified_by'];

                $request['application_id'] = $directrecruitmentApplicationAttachment['application_id'];
                $request['action'] = Config::get('services.APPLICATION_SUMMARY_ACTION')[2];
                $this->applicationSummaryServices->deleteStatus($request);
            }
            $directRecruitmentApplicationChecklist->save();
        }
        return $res;
    }
    /**
     * @param $request
     * @return mixed
     */
    public function list($request) : mixed
    {
        if(!($this->validationServices->validate($request,['application_id' => 'required']))){
            return [
                'validate' => $this->validationServices->errors()
            ];
        }
        return $this->directRecruitmentApplicationChecklist
        ->leftJoin('application_checklist_attachments', 'application_checklist_attachments.application_checklist_id',  'directrecruitment_application_checklist.id')
        ->leftJoin('directrecruitment_applications', 'directrecruitment_applications.id', 'directrecruitment_application_checklist.application_id')
        ->leftJoin('crm_prospect_services', function($join) use ($request){
            $join->on('crm_prospect_services.crm_prospect_id', '=', 'directrecruitment_applications.crm_prospect_id')
            ->where('crm_prospect_services.sector_id',$request['sector_id']);
          })
        ->leftJoin('document_checklist', 'document_checklist.id', 'crm_prospect_services.sector_id')
        /*->leftJoin('document_checklist', function($join) use ($request){
            $join->on('document_checklist.id', '=', 'application_checklist_attachments.document_checklist_id')
            ->where('document_checklist.sector_id',$request['sector_id'])
            ->orWhereNull('application_checklist_attachments.document_checklist_id');
          })*/
        ->where('directrecruitment_application_checklist.application_id',$request['application_id'])
        ->with(["applicationChecklistAttachments" => function($attachment) use ($request){
            $attachment->where('application_id',$request['application_id']);
        }])->orderBy('directrecruitment_application_checklist.created_at','DESC')
        ->select('directrecruitment_application_checklist.id', 'directrecruitment_application_checklist.application_id', 'directrecruitment_application_checklist.application_checklist_status', 'directrecruitment_application_checklist.submitted_on', 'directrecruitment_application_checklist.modified_on', 'directrecruitment_application_checklist.created_by', 'directrecruitment_application_checklist.modified_by', 'directrecruitment_application_checklist.created_at', 'directrecruitment_application_checklist.updated_at', 'directrecruitment_application_checklist.deleted_at', 'directrecruitment_application_checklist.remarks', 'document_checklist.sector_id','document_checklist.document_title')
        ->distinct('directrecruitment_application_checklist.id')
        ->paginate(Config::get('services.paginate_row'));
    }
}
