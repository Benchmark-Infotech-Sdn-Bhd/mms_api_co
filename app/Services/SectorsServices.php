<?php

namespace App\Services;

use App\Models\Sectors;
use App\Services\ValidationServices;
use Illuminate\Support\Facades\Config;

class SectorsServices
{
    public const DEFAULT_VALUE = 0;
    public const STATUS_PENDING = 'Pending';
    
    public const MESSAGE_DATA_NOT_FOUND = 'Data not found';
    public const MESSAGE_DELETED_SUCCESSFULLY = 'Deleted Successfully';
    public const MESSAGE_UPDATED_SUCCESSFULLY = 'Updated Successfully';

    private Sectors $sectors;
    private ValidationServices $validationServices;

    /**
     * SectorsServices constructor.
     * 
     * @param Sectors $sectors Instance of the Sectors class
     * @param ValidationServices $validationServices Instance of the ValidationServices class
     * 
     * @return void
     * 
     */
    public function __construct(
        Sectors             $sectors,
        ValidationServices  $validationServices
    )
    {
        $this->sectors = $sectors;
        $this->validationServices = $validationServices;
    }

    /**
     * Retrieve the sector record based on requested data.
     *
     * 
     * @param array $request
     *              company_id (int) ID of the user company
     *              id (int) ID of the sector
     * 
     * @return mixed Returns the sector data

     */
    private function getSector($request)
    {
        return $this->sectors->where('company_id', $request['company_id'])->find($request['id']);
    }

    /**
     * Validate the given request data.
     *
     * @param array $request The request data to be validated.
     * @return array|bool Returns an array with 'error' as key and validation error messages as value if validation fails.
     *                   Returns true if validation passes.
     */
    private function validateCreateRequest($request): array|bool
    {
        if(!($this->validationServices->validate($request,$this->sectors->rules))){
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
     * @return array|bool Returns an array with 'error' as key and validation error messages as value if validation fails.
     *                   Returns true if validation passes.
     */
    private function validateUpdateRequest($request): array|bool
    {
        if(!($this->validationServices->validate($request,$this->sectors->rulesForUpdation))){
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
     * @return array|bool Returns an array with 'error' as key and validation error messages as value if validation fails.
     *                   Returns true if validation passes.
     */
    private function validateIdRequest($request): array|bool
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
     * @return array|bool Returns an array with 'error' as key and validation error messages as value if validation fails.
     *                   Returns true if validation passes.
     */
    private function validateUpdateChecklistStatusRequest($request): array|bool
    {
        if(!($this->validationServices->validate($request,['id' => 'required','checklist_status' => 'required']))){
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
     * @return array|bool Returns an array with 'error' as key and validation error messages as value if validation fails.
     *                   Returns true if validation passes.
     */
    private function validateUpdateStatusRequest($request): array|bool
    {
        if(!($this->validationServices->validate($request,['id' => 'required','status' => 'required|regex:/^[0-1]+$/|max:1']))){
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
     * @return array|bool Returns an array with 'error' as key and validation error messages as value if validation fails.
     *                   Returns true if validation passes.
     */
    private function validateListRequest($request): array|bool
    {
        $search = $request['search_param'] ?? '';
        if(!empty($search)){
            if(!($this->validationServices->validate($request,['search_param' => 'required|min:3']))){
                return [
                    'validate' => $this->validationServices->errors()
                ];
            }
        }

        return true;
    }

    /**
     * Create the sector
     * 
     * @param $request The request data containing the create sector data
     * 
     * @return mixed Returns the created sector record.
     */
    public function create($request) : mixed
    {
        $validationResult = $this->validateCreateRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }
        return $this->createSector($request);
    }

    /**
     * create sector.
     *
     * @param array $request The request data containing the 'sector_name', 'sub_sector_name', 'created_by',  'company_id' key.
     * 
     * @return mixed Returns the created sector record.
     */
    private function createSector($request): mixed
    {
        return $this->sectors->create([
            'sector_name' => $request['sector_name'] ?? '',
            'sub_sector_name' => $request['sub_sector_name'] ?? '',
            'checklist_status' => self::STATUS_PENDING,
            'created_by'    => $request['created_by'] ?? self::DEFAULT_VALUE,
            'modified_by'   => $request['created_by'] ?? self::DEFAULT_VALUE,
            'company_id' => $request['company_id'] ?? self::DEFAULT_VALUE
        ]);
    }

    /**
     * Update the sector
     * 
     * @param $request The request data containing the update sector data
     * 
     * @return mixed Returns an array with two keys: 'isUpdated' and 'message'
     */
    public function update($request) : mixed
    {
        $validationResult = $this->validateUpdateRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }
        $sector = $this->getSector($request);
        if(is_null($sector)){
            return [
                "isUpdated" => false,
                "message"=> self::MESSAGE_DATA_NOT_FOUND
            ];
        }
        return [
            "isUpdated" => $this->updateSector($request,$sector),
            "message"=> self::MESSAGE_UPDATED_SUCCESSFULLY
        ];
    }

    /**
     * update the sector.
     *
     * @param array $request The request data containing the 'id' , 'sector_name', 'sub_sector_name', 'checklist_status', 'modified_by' key.
     * @param object $sector The sector object
     * 
     * @return mixed Returns the updated sector record.
     */
    private function updateSector($request,$sector): mixed
    {
        return $sector->update([
            'id' => $request['id'],
            'sector_name' => $request['sector_name'] ?? $sector['sector_name'],
            'sub_sector_name' => $request['sub_sector_name'] ?? $sector['sub_sector_name'],
            'checklist_status' =>$sector['checklist_status'],
            'modified_by'   => $request['modified_by'] ?? $sector['modified_by']
        ]);
    }

    /**
     * Delete the sector
     * 
     * @param $request The request data containing the 'id', 'company_id' key
     * 
     * @return mixed Returns an array with two keys: 'isDeleted' and 'message'
     */
    public function delete($request) : mixed
    {
        $validationResult = $this->validateIdRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }

        $sector = $this->getSector($request);
        if(is_null($sector)){
            return [
                "isDeleted" => false,
                "message" => self::MESSAGE_DATA_NOT_FOUND
            ];
        }
        $sector->documentChecklist()->delete();
        return [
            "isDeleted" => $sector->delete(),
            "message" => self::MESSAGE_DELETED_SUCCESSFULLY
        ];
    }
    /**
     * Show the sector
     * 
     * @param $request The request data containing the 'id', 'company_id' key
     * 
     * @return mixed Returns the sector record
     */
    public function show($request) : mixed
    {
        $validationResult = $this->validateIdRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }
        return $this->sectors->whereIn('company_id', $request['company_id'])->find($request['id']);
    }
    /**
     * List the sector based on request data
     * 
     * @param array $companyId
     * 
     * @return mixed Returns the list of sector.
     */
    public function dropdown($companyId) : mixed
    {
        return $this->sectors->where('status', 1)
                ->whereIn('company_id', $companyId)
                ->select('id','sector_name')
                ->orderBy('sectors.created_at','DESC')
                ->get();
    }
    /**
     * Update the sector checklist status
     * 
     * @param $request The request data containing the 'id', 'checklist_status' key
     * 
     * @return array Returns an array with two keys: 'isUpdated' and 'message'
     */
    public function updateChecklistStatus($request) : array
    {
        $validationResult = $this->validateUpdateChecklistStatusRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }

        $sector = $this->sectors->find($request['id']);
        if(is_null($sector)){
            return [
                "isUpdated" => false,
                "message"=> self::MESSAGE_DATA_NOT_FOUND
            ];
        }
        $sector->checklist_status = $request['checklist_status'];
        return  [
            "isUpdated" => $sector->save() == 1,
            "message" => self::MESSAGE_UPDATED_SUCCESSFULLY
        ];
    }
    /**
     * List the sector
     * 
     * @param $request The request data containing the 'company_id', 'search' key
     * 
     * @return mixed Returns the paginated list of sector.
     */
    public function list($request) : mixed
    {
        $validationResult = $this->validateListRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }
        return $this->sectors->whereIn('company_id', $request['company_id'])
        ->where(function ($query) use ($request) {
            $search = $request['search_param'] ?? '';
            if (!empty($search)) {
                $query->where('sector_name', 'like', "%{$search}%");
            }
        })->select('id','sector_name','sub_sector_name','checklist_status','status')
        ->orderBy('sectors.created_at','DESC')
        ->paginate(Config::get('services.paginate_row'));
    }
    /**
     * Update sector status
     * 
     * @param $request The request data containing the 'id', 'status' key
     * 
     * @return array Returns an array with two keys: 'isUpdated' and 'message'
     */
    public function updateStatus($request) : array
    {
        $validationResult = $this->validateUpdateStatusRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }

        $sector = $this->getSector($request);
        if(is_null($sector)){
            return [
                "isUpdated" => false,
                "message"=> self::MESSAGE_DATA_NOT_FOUND
            ];
        }
        $sector->status = $request['status'];
        return [
            "isUpdated" => $sector->save() == 1,
            "message" => self::MESSAGE_UPDATED_SUCCESSFULLY
        ];
    }
}
