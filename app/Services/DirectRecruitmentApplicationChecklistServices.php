<?php

namespace App\Services;

use App\Models\DirectRecruitmentApplicationChecklist;
use App\Services\ValidationServices;
use Illuminate\Support\Facades\Config;

class DirectRecruitmentApplicationChecklistServices
{
    private DirectRecruitmentApplicationChecklist $directRecruitmentApplicationChecklist;
    private ValidationServices $validationServices;
    /**
     * DirectRecruitmentApplicationChecklistServices constructor.
     * @param DirectRecruitmentApplicationChecklist $directRecruitmentApplicationChecklist
     * @param ValidationServices $validationServices
     */
    public function __construct(DirectRecruitmentApplicationChecklist $directRecruitmentApplicationChecklist,ValidationServices $validationServices)
    {
        $this->directRecruitmentApplicationChecklist = $directRecruitmentApplicationChecklist;
        $this->validationServices = $validationServices;
    }

    /**
     * @param $request
     * @return mixed
     */
    public function create($request) : mixed
    {
        if(!($this->validationServices->validate($request,$this->directRecruitmentApplicationChecklist->rules))){
            return [
                'validate' => $this->validationServices->errors()
            ];
        }
        $directRecruitmentApplicationChecklist = $this->directRecruitmentApplicationChecklist->create([
            'application_id' => (int)$request['application_id'] ?? 0,
            'item_name' => $request['item_name'] ?? 'Document Checklist',
            'application_checklist_status' => $request['application_checklist_status'] ?? 'Pending',
            'created_by'    => $request['created_by'] ?? 0,
            'modified_by'   => $request['created_by'] ?? 0
        ]);
        return $directRecruitmentApplicationChecklist;
    }
    /**
     * @param $request
     * @return mixed
     */
    public function update($request) : mixed
    {
        if(!($this->validationServices->validate($request,$this->directRecruitmentApplicationChecklist->rulesForUpdation))){
            return [
                'validate' => $this->validationServices->errors()
            ];
        }
        $directRecruitmentApplicationChecklist = $this->directRecruitmentApplicationChecklist->find($request['id']);
        if(is_null($directRecruitmentApplicationChecklist)){
            return [
                "isUpdated" => false,
                "message"=> "Data not found"
            ];
        }
        return [
            "isUpdated" => $directRecruitmentApplicationChecklist->update([
                'id' => $request['id'],
                'application_id' => (int)($request['application_id'] ?? $directRecruitmentApplicationChecklist['application_id']),
                'item_name' => $request['item_name'] ?? $directRecruitmentApplicationChecklist['item_name'],
                'application_checklist_status' => $request['application_checklist_status'] ?? $directRecruitmentApplicationChecklist['application_checklist_status'],
                'remarks' => $request['remarks'] ?? $directRecruitmentApplicationChecklist['remarks'],
                'file_url' => $request['file_url'] ?? $directRecruitmentApplicationChecklist['file_url'],
                'modified_by'   => $request['modified_by'] ?? $directRecruitmentApplicationChecklist['modified_by']
            ]),
            "message"=> "Updated Successfully"
        ];
    }
    /**
     * @param $request
     * @return mixed
     */
    public function show($request) : mixed
    {
        if(!($this->validationServices->validate($request,['id' => 'required']))){
            return [
                'validate' => $this->validationServices->errors()
            ];
        }
        return $this->directRecruitmentApplicationChecklist->findOrFail($request['id']);
    }
    /**
     * @param $request
     * @return array
     */
    public function updateStatusBasedOnApplication($request) : array
    {
        if(!($this->validationServices->validate($request,['application_id' => 'required','status' => 'required']))){
            return [
                'validate' => $this->validationServices->errors()
            ];
        }
        $directRecruitmentApplicationChecklist = $this->directRecruitmentApplicationChecklist->where('application_id',$request['application_id'])->first();
        if(is_null($directRecruitmentApplicationChecklist)){
            return [
                "isUpdated" => false,
                "message"=> "Data not found"
            ];
        }
        $directRecruitmentApplicationChecklist->application_checklist_status = $request['status'];
        $directRecruitmentApplicationChecklist->modified_by = $request['user_id'] ?? $directRecruitmentApplicationChecklist['modified_by'];
        return  [
            "isUpdated" => $directRecruitmentApplicationChecklist->save() == 1,
            "message" => "Updated Successfully"
        ];
    }
    /**
     * @param $request
     * @return mixed
     */
    public function showBasedOnApplication($request) : mixed
    {
        if(!($this->validationServices->validate($request,['application_id' => 'required']))){
            return [
                'validate' => $this->validationServices->errors()
            ];
        }
        $directRecruitmentApplicationChecklist = $this->directRecruitmentApplicationChecklist->where('application_id',$request['application_id'])->first();
        if(is_null($directRecruitmentApplicationChecklist)){
            return [
                "isPresent" => false,
                "message"=> "Data not found"
            ];
        }
        return $directRecruitmentApplicationChecklist;
    }
}
