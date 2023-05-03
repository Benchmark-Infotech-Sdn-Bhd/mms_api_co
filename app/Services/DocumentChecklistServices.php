<?php

namespace App\Services;

use App\Models\DocumentChecklist;
use App\Services\ValidationServices;
use Illuminate\Support\Facades\Config;
use App\Services\SectorsServices;

class DocumentChecklistServices
{
    private DocumentChecklist $documentChecklist;
    private ValidationServices $validationServices;
    private SectorsServices $sectorsServices;
    /**
     * DocumentChecklistServices constructor.
     * @param DocumentChecklist $documentChecklist
     * @param ValidationServices $validationServices
     * @param SectorsServices $sectorsServices
     */
    public function __construct(DocumentChecklist $documentChecklist,ValidationServices $validationServices,SectorsServices $sectorsServices)
    {
        $this->documentChecklist = $documentChecklist;
        $this->validationServices = $validationServices;
        $this->sectorsServices = $sectorsServices;
    }

    /**
     * @param $request
     * @return mixed
     */
    public function create($request) : mixed
    {
        if(!($this->validationServices->validate($request,$this->documentChecklist->rules))){
            return [
                'validate' => $this->validationServices->errors()
            ];
        }
        $checklist = $this->documentChecklist->create([
            'sector_id' => (int)$request['sector_id'] ?? 0,
            'document_title' => $request['document_title'] ?? '',
            'created_by'    => $request['created_by'] ?? 0,
            'modified_by'   => $request['created_by'] ?? 0
        ]);
        $count = $this->documentChecklist->whereNull('deleted_at')
        ->where('sector_id','=',$request['sector_id'])->count('id');
        if($count == 1){
        $result =  $this->sectorsServices->updateChecklistStatus([ 'id' => $request['sector_id'], 'checklist_status' => 'Done' ]);
        }
        return $checklist;
    }
    /**
     * @param $request
     * @return mixed
     */
    public function update($request) : mixed
    {
        if(!($this->validationServices->validate($request,$this->documentChecklist->rulesForUpdation))){
            return [
                'validate' => $this->validationServices->errors()
            ];
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
                'sector_id' => (int)$request['sector_id'] ?? $documentChecklist['sector_id'],
                'document_title' => $request['document_title'] ?? $documentChecklist['document_title'],
                'modified_by'   => $request['modified_by'] ?? $documentChecklist['modified_by']
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
            return [
                'validate' => $this->validationServices->errors()
            ];
        }
        $documentChecklist = $this->documentChecklist->find($request['id']);
        if(is_null($documentChecklist)){
            return [
                "isDeleted" => false,
                "message" => "Data not found"
            ];
        }
        $res = [
            "isDeleted" => $documentChecklist->delete(),
            "message" => "Deleted Successfully"
        ];
        if($res['isDeleted']){
            $count = $this->documentChecklist->whereNull('deleted_at')
            ->where('sector_id','=',$documentChecklist['sector_id'])->count('id');
            if($count == 0){
            $result =  $this->sectorsServices->updateChecklistStatus([ 'id' => $documentChecklist['sector_id'], 'checklist_status' => 'Pending' ]);
            }
        }
        return $res;
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
        return $this->documentChecklist->findOrFail($request['id']);
    }
    /**
     * @return mixed
     */
    public function retrieveAll() : mixed
    {
        return $this->documentChecklist->orderBy('document_checklist.created_at','DESC')
        ->paginate(Config::get('services.paginate_row'));
    }
    /**
     * @param $request
     * @return mixed
     */
    public function list($request) : mixed
    {
        if(!($this->validationServices->validate($request,['sector_id' => 'required']))){
            return [
                'validate' => $this->validationServices->errors()
            ];
        }
        return $this->documentChecklist->where('sector_id',$request['sector_id'])
        ->select('id','document_title')
        ->orderBy('document_checklist.created_at','DESC')
        ->paginate(Config::get('services.paginate_row'));
    }
}
