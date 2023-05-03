<?php


namespace App\Services;

use App\Models\DirectrecruitmentApplications;
use App\Models\DirectrecruitmentApplicationAttachments;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Services\DirectRecruitmentApplicationChecklistServices;

class DirectRecruitmentServices
{
    /**
     * @var DirectrecruitmentApplications
     */
    private DirectrecruitmentApplications $directrecruitmentApplications;
    /**
     * @var DirectrecruitmentApplicationAttachments
     */
    private DirectrecruitmentApplicationAttachments $directrecruitmentApplicationAttachments;
    /**
     * @var Storage
     */
    private Storage $storage;
    /**
     * @var DirectRecruitmentApplicationChecklistServices
     */
    private DirectRecruitmentApplicationChecklistServices $directRecruitmentApplicationChecklistServices;
    public function __construct(DirectrecruitmentApplications $directrecruitmentApplications, DirectrecruitmentApplicationAttachments $directrecruitmentApplicationAttachments, 
    Storage $storage,DirectRecruitmentApplicationChecklistServices $directRecruitmentApplicationChecklistServices)
    {
        $this->directrecruitmentApplications = $directrecruitmentApplications;
        $this->directrecruitmentApplicationAttachments = $directrecruitmentApplicationAttachments;
        $this->storage = $storage;
        $this->directRecruitmentApplicationChecklistServices = $directRecruitmentApplicationChecklistServices;
    }
    /**
     * @param $request
     * @return mixed | boolean
     */
    public function inputValidation($request)
    {
        if(!($this->directrecruitmentApplications->validate($request->all()))){
            return $this->directrecruitmentApplications->errors();
        }
        return false;
    }
    /**
     * @param $request
     * @return mixed | boolean
     */
    public function updateValidation($request)
    {
        if(!($this->directrecruitmentApplications->validateUpdation($request->all()))){
            return $this->directrecruitmentApplications->errors();
        }
        return false;
    }
     /**
     *
     * @param $request
     * @return mixed
     */
    public function showProposal($request) : mixed
    {
        return $this->directrecruitmentApplications::with(['applicationAttachment' => function ($query) {
            $query->orderBy('created_at', 'desc');
        }])->find($request['id']);
    }
    /**
     *
     * @param $request
     * @return array
     */
    public function submitProposal($request): array
    {    
        $data = $this->directrecruitmentApplications::findorfail($request['id']);
        $input = $request->all();
        $user = JWTAuth::parseToken()->authenticate();
        $input['modified_by'] = $user['id']; 
        $input['status'] = 'Proposal Submitted'; 
        if (request()->hasFile('attachment')){
            foreach($request->file('attachment') as $file){
                $fileName = $file->getClientOriginalName();
                $filePath = '/directRecruitment/proposal/' . $fileName; 
                $linode = $this->storage::disk('linode');
                $linode->put($filePath, file_get_contents($file));
                $fileUrl = $this->storage::disk('linode')->url($filePath);
                $this->directrecruitmentApplicationAttachments::create([
                        "file_id" => $request['id'],
                        "file_name" => $fileName,
                        "file_type" => 'proposal',
                        "file_url" =>  $fileUrl         
                    ]);  
            }
        }
        $res = $data->update($input);
        if($res){
            $applicationChecklist = $this->directRecruitmentApplicationChecklistServices->create(
                ['application_id' => $request['id'],
                'item_name' => 'Document Checklist',
                'application_checklist_status' => 'Pending',
                'created_by' => $user['id']]
            );
        }
        return  [
            "isUpdated" => $res,
            "message" => "Updated Successfully"
        ];
    }
}