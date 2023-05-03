<?php


namespace App\Services;

use App\Models\DirectrecruitmentApplications;
use App\Models\DirectrecruitmentApplicationAttachments;
use Illuminate\Support\Facades\Storage;
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
    public function deleteAttachment($request): mixed
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