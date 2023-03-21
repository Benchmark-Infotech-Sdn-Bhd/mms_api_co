<?php

namespace App\Services;

use App\Models\DocumentChecklist;
use App\Services\ValidationServices;

class DocumentChecklistServices
{
    private DocumentChecklist $documentChecklist;
    private ValidationServices $validationServices;
    /**
     * DocumentChecklistServices constructor.
     * @param DocumentChecklist $documentChecklist
     * @param ValidationServices $validationServices
     */
    public function __construct(DocumentChecklist $documentChecklist,ValidationServices $validationServices)
    {
        $this->documentChecklist = $documentChecklist;
        $this->validationServices = $validationServices;
    }

    /**
     * @param $request
     * @return mixed
     */
    public function create($request) : mixed
    {
        if(!($this->validationServices->validate($request,$this->documentChecklist->rules))){
            return $this->validationServices->errors();
        }
        return $this->documentChecklist->create([
            'sector_id' => $request['sector_id'] ?? 0,
            'document_title' => $request['document_title'] ?? ''
        ]);
    }
    /**
     * @param $request
     * @return mixed
     */
    public function update($request) : mixed
    {
        if(!($this->validationServices->validate($request,$this->documentChecklist->rulesForUpdation))){
            return $this->validationServices->errors();
        }
        $documentChecklist = $this->documentChecklist->find($request['id']);
        if(is_null($documentChecklist)){
            return [
                "isUpdated" => false,
                "message"=> "Data not found"
            ];
        }
        return [
            "isUpdated" => $documentChecklist->update([
                'id' => $request['id'],
                'sector_id' => $request['sector_id'] ?? 0,
                'document_title' => $request['document_title'] ?? ''
            ]),
            "message"=> "Updated Successfully"
        ];
    }
    /**
     * @param $request
     * @return mixed
     */
    public function delete($request) : mixed
    {
        if(!($this->validationServices->validate($request,['id' => 'required']))){
            return $this->validationServices->errors();
        }
        $documentChecklist = $this->documentChecklist->find($request['id']);
        if(is_null($documentChecklist)){
            return [
                "isDeleted" => false,
                "message" => "Data not found"
            ];
        }
        return [
            "isDeleted" => $documentChecklist->delete(),
            "message" => "Deleted Successfully"
        ];
    }
    /**
     * @param $request
     * @return mixed
     */
    public function retrieveBySector($request) : mixed
    {
        if(!($this->validationServices->validate($request,['sector_id' => 'required']))){
            return $this->validationServices->errors();
        }
        return $this->documentChecklist->where('sector_id',$request['sector_id'])->get();
    }
}
