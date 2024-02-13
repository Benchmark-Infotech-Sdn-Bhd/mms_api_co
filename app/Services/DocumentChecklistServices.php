<?php

namespace App\Services;

use App\Models\DocumentChecklist;
use App\Models\Sectors;
use App\Services\ValidationServices;
use Illuminate\Support\Facades\Config;
use App\Services\SectorsServices;

class DocumentChecklistServices
{
    public const STATUS_DONE = 'Done';
    public const STATUS_PENDING = 'Pending';
    public const MESSAGE_DATA_NOT_FOUND = "Data not found";
    public const MESSAGE_DELETED_SUCCESSFULLY = "Deleted Successfully";
    public const MESSAGE_UPDATED_SUCCESSFULLY = 'Updated Successfully';
    public const DEFAULT_INTEGER_VALUE_ZERO = 0;
    public const DEFAULT_INTEGER_VALUE_ONE = 1;

    public const ERROR_INVALID_USER = ['InvalidUser' => true];

    /**
     * @var DocumentChecklist
     */
    private DocumentChecklist $documentChecklist;

    /**
     * @var ValidationServices
     */
    private ValidationServices $validationServices;

    /**
     * @var SectorsServices
     */
    private SectorsServices $sectorsServices;

    /**
     * @var Sectors
     */
    private Sectors $sectors;

    /**
     * Constructor method.
     * 
     * @param DocumentChecklist $documentChecklist Instance of the DocumentChecklist class.
     * @param ValidationServices $validationServices Instance of the ValidationServices class.
     * @param SectorsServices $sectorsServices Instance of the SectorsServices class.
     * @param Sectors $sectors Instance of the Sectors class.
     */
    public function __construct(
        DocumentChecklist $documentChecklist,
        ValidationServices $validationServices,
        SectorsServices $sectorsServices,
        Sectors $sectors
    )
    {
        $this->documentChecklist = $documentChecklist;
        $this->validationServices = $validationServices;
        $this->sectorsServices = $sectorsServices;
        $this->sectors = $sectors;
    }

    /**
     * @param $request
     * @return mixed
     */
    public function create($request) : mixed
    {
        $validationResult = $this->createValidateRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }
        
        $sectorDetails = $this->showCompanySectors($request);
        if(is_null($sectorDetails)) {
            return self::ERROR_INVALID_USER;
        }
        
        $checklist = $this->createDocumentChecklist($request);

        $count = $this->getDocumentChecklistCount($request);
        if($count == self::DEFAULT_INTEGER_VALUE_ONE){
            $result = $this->updateChecklistStatus($request, self::STATUS_DONE);
        }

        return $checklist;
    }
    /**
     * @param $request
     * @return mixed
     */
    public function update($request) : mixed
    {
        $validationResult = $this->updateValidateRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }
        
        $documentChecklist = $this->showDocumentChecklist($request);
        if(is_null($documentChecklist)){
            return [
                "isUpdated" => false,
                "message"=> self::MESSAGE_DATA_NOT_FOUND
            ];
        }

        $sectorDetails = $this->showCompanySectors(['company_id' => $request['company_id'], 'sector_id' => $documentChecklist->sector_id]);
        if(is_null($sectorDetails)) {
            return self::ERROR_INVALID_USER;
        }

        return $this->updateDocumentChecklist($documentChecklist, $request);
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

        $documentChecklist = $this->showDocumentChecklist($request)
        if(is_null($documentChecklist)){
            return [
                "isDeleted" => false,
                "message" => self::MESSAGE_DATA_NOT_FOUND
            ];
        }

        $sectorDetails = $this->showCompanySectors(['company_id' => $request['company_id'], 'sector_id' => $documentChecklist->sector_id]);
        if(is_null($sectorDetails)) {
            return self::ERROR_INVALID_USER;
        }

        $res = [
            "isDeleted" => $documentChecklist->delete(),
            "message" => self::MESSAGE_DELETED_SUCCESSFULLY
        ];

        if($res['isDeleted']){
            $count = $this->getDocumentChecklistCount(['sector_id' => $documentChecklist['sector_id']]);
            if($count == self::DEFAULT_INTEGER_VALUE_ZERO){
                $result =  $this->updateChecklistStatus(['sector_id' => $documentChecklist['sector_id']], self::STATUS_PENDING);
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
        $validationResult = $this->showValidateRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }

        $documentChecklist = $this->showDocumentChecklist($request);
        if(is_null($documentChecklist)){
            return [
                "error" => true,
                "message" => self::MESSAGE_DATA_NOT_FOUND
            ];
        }

        $sectorDetails = $this->showCompanySectors(['company_id' => $request['company_id'], 'sector_id' => $documentChecklist->sector_id]);
        if(is_null($sectorDetails)) {
            return self::ERROR_INVALID_USER;
        }

        return $this->showDocumentChecklist($request);
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

        $sectorDetails = $this->showCompanySectors(['company_id' => $request['company_id'], 'sector_id' => $request['sector_id']]);;
        if(is_null($sectorDetails)) {
            return self::ERROR_INVALID_USER;
        }

        return $this->documentChecklist->where('sector_id',$request['sector_id'])
        ->select('id','document_title')
        ->orderBy('document_checklist.created_at','DESC')
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
        if(!($this->validationServices->validate($request,$this->documentChecklist->rules))){
            return [
                'validate' => $this->validationServices->errors()
            ];
        }

        return true;
    }

    private function showCompanySectors($request)
    {
        return $this->sectors->where('company_id', $request['company_id'])->find($request['sector_id']);
    }

    private function createDocumentChecklist($request)
    {
        return $this->documentChecklist->create([
            'sector_id' => (int)$request['sector_id'] ?? self::DEFAULT_INTEGER_VALUE_ZERO,
            'document_title' => $request['document_title'] ?? '',
            'remarks' => $request['remarks'] ?? '',
            'created_by'    => $request['created_by'] ?? self::DEFAULT_INTEGER_VALUE_ZERO,
            'modified_by'   => $request['created_by'] ?? self::DEFAULT_INTEGER_VALUE_ZERO
        ]);
    }

    private function getDocumentChecklistCount($request)
    {
        return $this->documentChecklist->whereNull('deleted_at')
            ->where('sector_id','=',$request['sector_id'])->count('id');
    }

    private function updateChecklistStatus($request, $status)
    {
        return $this->sectorsServices->updateChecklistStatus([ 'id' => $request['sector_id'], 'checklist_status' => $status]);
    }

    /**
     * Validate the given request data.
     *
     * @param array $request The request data to be validated.
     * @return array|bool Returns an array with 'error' as key and validation error messages as value if validation fails. | Returns true if validation passes.
     */
    private function updateValidateRequest($request): array|bool
    {
        if(!($this->validationServices->validate($request,$this->documentChecklist->rulesForUpdation))){
            return [
                'validate' => $this->validationServices->errors()
            ];
        }

        return true;
    }

    private function showDocumentChecklist($request)
    {
        return $this->documentChecklist->find($request['id']);
    }

    private function updateDocumentChecklist($documentChecklist, $request)
    {
        return [
            "isUpdated" => $documentChecklist->update([
                'id' => $request['id'],
                'sector_id' => (int)$request['sector_id'] ?? $documentChecklist['sector_id'],
                'document_title' => $request['document_title'] ?? $documentChecklist['document_title'],
                'remarks' => $request['remarks'] ?? $documentChecklist['remarks'],
                'modified_by'   => $request['modified_by'] ?? $documentChecklist['modified_by']
            ]),
            "message"=> self::MESSAGE_UPDATED_SUCCESSFULLY
        ];
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

    /**
     * Validate the given request data.
     *
     * @param array $request The request data to be validated.
     * @return array|bool Returns an array with 'error' as key and validation error messages as value if validation fails. | Returns true if validation passes.
     */
    private function showValidateRequest($request): array|bool
    {
        if(!($this->validationServices->validate($request,['id' => 'required']))){
            return [
                'validate' => $this->validationServices->errors()
            ];
        }

        return true;
    }

    /**
     * Validate the given request data.
     *
     * @param array $request The request data to be validated.
     * @return array|bool Returns an array with 'error' as key and validation error messages as value if validation fails. | Returns true if validation passes.
     */
    private function listValidateRequest($request): array|bool
    {
        if(!($this->validationServices->validate($request,['sector_id' => 'required']))){
            return [
                'validate' => $this->validationServices->errors()
            ];
        }

        return true;
    }
}
