<?php


namespace App\Services;

use App\Models\DirectrecruitmentApplications;
use App\Models\DirectrecruitmentApplicationAttachments;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Config;
use Tymon\JWTAuth\Facades\JWTAuth;

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
    public function __construct(DirectrecruitmentApplications $directrecruitmentApplications, DirectrecruitmentApplicationAttachments $directrecruitmentApplicationAttachments, Storage $storage)
    {
        $this->directrecruitmentApplications = $directrecruitmentApplications;
        $this->directrecruitmentApplicationAttachments = $directrecruitmentApplicationAttachments;
        $this->storage = $storage;
    }
    /**
     * @param $request
     * @return mixed | void
     */
    public function inputValidation($request)
    {
        if(!($this->directrecruitmentApplications->validate($request->all()))){
            return $this->directrecruitmentApplications->errors();
        }
    }
    /**
     * @param $request
     * @return mixed | void
     */
    public function updateValidation($request)
    {
        if(!($this->directrecruitmentApplications->validateUpdation($request->all()))){
            return $this->directrecruitmentApplications->errors();
        }
    }
	 /**
     *
     * @param $request
     * @return mixed
     */
    public function addProposal($request): mixed
    {   
        $user = JWTAuth::parseToken()->authenticate();
        $request['created_by'] = $user['id'];
        $proposalData = $this->directrecruitmentApplications::create([
            'crm_prospect_id' => $request["crm_prospect_id"],
            'service_id' => $request["service_id"],
            'quota_applied' => $request["quota_applied"],
            'person_incharge' => $request["person_incharge"],
            'cost_quoted' => $request["cost_quoted"],
            'remarks' => $request["remarks"],
            'created_by' => $request["created_by"],
        ]);
        $proposalId = $proposalData->id;
        if (request()->hasFile('attachment')){
            foreach($request->file('attachment') as $file){
                $fileName = $file->getClientOriginalName();
                $filePath = '/directRecruitment/proposal/' . $fileName; 
                $linode = $this->storage::disk('linode');
                $linode->put($filePath, file_get_contents($file));
                $fileUrl = $this->storage::disk('linode')->url($filePath);
                $this->directrecruitmentApplicationAttachments::create([
                        "file_id" => $proposalId,
                        "file_name" => $fileName,
                        "file_type" => 'proposal',
                        "file_url" =>  $fileUrl         
                    ]);  
            }
        }
        return $proposalData;
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
     * @return mixed
     */
    public function updateProposal($request): mixed
    {    
        $data = $this->directrecruitmentApplications::findorfail($request['id']);
        $input = $request->all();
        $user = JWTAuth::parseToken()->authenticate();
        $input['modified_by'] = $user['id']; 
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
        return  [
            "isUpdated" => $data->update($input),
            "message" => "Updated Successfully"
        ];
    }

    /**
     *
     * @param $request
     * @return mixed
     */    
    public function deleteAttachment($request)
    {   
        $data = $this->directrecruitmentApplicationAttachments::find($request['id']); 
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