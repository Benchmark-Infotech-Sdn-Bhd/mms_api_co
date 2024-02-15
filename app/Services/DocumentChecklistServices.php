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
     * 
     * @return void
     */
    public function __construct(
        DocumentChecklist      $documentChecklist,
        ValidationServices     $validationServices,
        SectorsServices        $sectorsServices,
        Sectors                $sectors
    )
    {
        $this->documentChecklist = $documentChecklist;
        $this->validationServices = $validationServices;
        $this->sectorsServices = $sectorsServices;
        $this->sectors = $sectors;
    }

    /**
     * Creates a new document check list from the given request data.
     * 
     * @param array $request The array containing document check list data.
     *                      The array should have the following keys:
     *                      - sector_id: The sector id of the document.
     *                      - document_title: The document title of the document.
     *                      - remarks: The remarks of the document.
     * @return mixed Returns an mixed with the following keys:
     * - "validate": An array of validation errors, if any.
     * - "InvalidUser" (boolean): A array returns InvalidUser if sectorDetails is null.
     * - "isSubmit": A object indicating if the document check list was successfully created.
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
     * Updates the document check list from the given request data.
     * 
     * @param array $request The array containing document check list data.
     *                      The array should have the following keys:
     *                      - sector_id: The sector id of the document.
     *                      - document_title: The document title of the document.
     *                      - remarks: The remarks of the document.
     * @return mixed Returns an mixed with the following keys:
     * - "validate": An array of validation errors, if any.
     * - "isUpdated" (boolean): A value returns false if documentChecklist is null.
     * - "isUpdated" (boolean): Indicates whether the data was updated. Always set to `false`.
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
     * Delete the document check list
     * 
     * @param array $request The array containing document id.
     * @return array Returns an array with the following keys:
     * - "validate": An array of validation errors, if any.
     * - "InvalidUser" (boolean): A value returns false if documentChecklist is null.
     * - "isDeleted" (boolean): Indicates whether the data was deleted. Always set to `false`.
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
     * Show the document check list
     * 
     * @param array $request The request data containing company id, document id.
     * @return mixed Returns an mixed with the following keys:
     * - "validate": An array of validation errors, if any.
     * - "Error" (boolean): A value returns true if document check list is null.
     * - "InvalidUser" (boolean): A value returns false if sectors is null.
     * - mixed Returns the document check list.
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
     * Returns a paginated list of document check list based on the given search request.
     * 
     * @param array $request The request data containing company id, sector id.
     * @return mixed Returns an mixed with the following keys:
     * - "validate": An array of validation errors, if any.
     * - "InvalidUser" (boolean): A value returns false if sectors is null.
     * - mixed Returns a paginated list of document check list.
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
    
    /**
     * Show the sectors.
     * 
     * @param array $request The request data containing sector id, company id
     * @return mixed Returns the sectors.
     */
    private function showCompanySectors($request)
    {
        return $this->sectors->where('company_id', $request['company_id'])->find($request['sector_id']);
    }
    
    /**
     * Creates a new document check list from the given request data.
     * 
     * @param array $request The array containing document check list data.
     *                      The array should have the following keys:
     *                      - sector_id: The sector id of the document.
     *                      - document_title: The document title of the document.
     *                      - remarks: The remarks of the document.
     *                      - created_by: The created document check list created by.
     *                      - modified_by: The updated document check list modified by.
     * 
     * @return document check list The newly created document check list object.
     */
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
    
    /**
     * Returns a count of document check list based on the given sector id.
     * 
     * @param array $request The request data containing sector id.
     * @return array Returns a count of document check list.
     */
    private function getDocumentChecklistCount($request)
    {
        return $this->documentChecklist->whereNull('deleted_at')
            ->where('sector_id','=',$request['sector_id'])->count('id');
    }
    
    /**
     * Updates the sectors status with the given request.
     * 
     * @param array $request The request data containing sector id.
     * @param string $status The status of the sector check list.
     * @return Object sectors The updated sectors object.
     */
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
    
    /**
     * Show the document check list.
     * 
     * @param array $request The request data containing document check list id
     * @return mixed Returns the document check list.
     */
    private function showDocumentChecklist($request)
    {
        return $this->documentChecklist->find($request['id']);
    }
    
    /**
     * Updates the document check list with the given request.
     * 
     * @param object $documentChecklist The documentChecklist object to be updated.
     * @param array $request The array containing document check list data.
     *                      The array should have the following keys:
     *                      - id: The updated id.
     *                      - sector_id: The updated sector id.
     *                      - document_title: The updated document title.
     *                      - remarks: The updated remarks.
     *                      - modified_by: The updated document modified by.
     * 
     * @return array Returns an array with the following keys:
     * - "isUpdated" (boolean): Indicates whether the data was updated. Always set to `false`.
     */
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
