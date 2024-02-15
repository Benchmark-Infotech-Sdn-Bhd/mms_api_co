<?php


namespace App\Services;

use App\Models\FeeRegistration;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Config;
use App\Models\Services;
use App\Models\FeeRegServices;
use App\Models\FeeRegSectors;
use App\Models\Sectors;
use Tymon\JWTAuth\Facades\JWTAuth;

class FeeRegistrationServices
{
    public const FEE_TYPE_STANDARD = 'standard';
    public const MESSAGE_DATA_NOT_FOUND = "Data not found";
    public const MESSAGE_UPDATED_SUCCESSFULLY = 'Updated Successfully';
    public const MESSAGE_DELETED_SUCCESSFULLY = "Deleted Successfully";

    public const ERROR_INVALID_USER = ['InvalidUser' => true];

    /**
     * @var FeeRegistration
     */
    private FeeRegistration $feeRegistration;

    /**
     * @var Services
     */
    private Services $services;

    /**
     * @var FeeRegServices
     */
    private FeeRegServices $feeRegServices;

    /**
     * @var FeeRegSectors
     */
    private FeeRegSectors $feeRegSectors;

    /**
     * @var Sectors
     */
    private Sectors $sectors;
    
    /**
     * Constructor method.
     * 
     * @param Sectors $sectors Instance of the Sectors class.
     * @param FeeRegServices $feeRegServices Instance of the FeeRegServices class.
     * @param FeeRegSectors $feeRegSectors Instance of the FeeRegSectors class.
     * @param Services $services Instance of the Services class.
     * @param FeeRegistration $feeRegistration Instance of the FeeRegistration class.
     * 
     * @return void
     */
    public function __construct(
        Sectors             $sectors,
        FeeRegServices      $feeRegServices,
        FeeRegSectors       $feeRegSectors,
        Services            $services,
        FeeRegistration     $feeRegistration
    )
    {
        $this->feeRegistration = $feeRegistration;
        $this->services = $services;
        $this->feeRegServices = $feeRegServices;
        $this->feeRegSectors = $feeRegSectors;
        $this->sectors = $sectors;
    }

    /**
     * Creates the validation rules for create a new fee registration.
     *
     * @return array The array containing the validation rules.
     */
    public function inputValidation($request)
    {
        if (!($this->feeRegistration->validate($request->all()))) {
            return $this->feeRegistration->errors();
        }
    }

    /**
     * Creates the validation rules for updating the fee registration.
     *
     * @return array The array containing the validation rules.
     */
    public function updateValidation($request)
    {
        if (strtolower($request["fee_type"]) == self::FEE_TYPE_STANDARD) {            
            if (!($this->feeRegistration->validateStandardUpdation($request->all()))) {
                return $this->feeRegistration->errors();
            }
        }
        else
        {
            if (!($this->feeRegistration->validateUpdation($request->all()))) {
                return $this->feeRegistration->errors();
            }
        }
    }

    /**
     * Creates a new fee registration from the given request data.
     * 
     * @param array $request The array containing fee registration data.
     * @return mixed Returns an mixed with the following keys:
     * - "InvalidUser" (boolean): A array returns InvalidUser if Sectors is null.
     * - "isSubmit": A object indicating if the fee registration was successfully created.
     */
    public function create($request): mixed
    {  
        $user = $this->getJwtUserAuthenticate();
        $request['company_id'] = $user['company_id'];
        $request['created_by'] = $user['id'];

        $existingSectors = $this->showCompanySectors($request);
        $existingSectors = array_column($existingSectors, 'id');
        $diffSectors = array_diff($request['sectors'], $existingSectors);
        if (!empty($diffSectors)) {
            return self::ERROR_INVALID_USER;
        }
        
        $feeRegistrationData = $this->createFeeRegistration($request);
        
        $this->createFeeRegServices($request, $feeRegistrationData->id);
        $this->createFeeRegSectors($request, $feeRegistrationData->id);

        return $feeRegistrationData;
    }

    /**
     * Returns a paginated list of fee registration with related fee registration services based on the given search request.
     * 
     * @param array $request The search request parameters and company id.
     * @return mixed Returns the paginated list of fee registration with related fee registration services.
     */
    public function list($request)
    {
        return $this->feeRegistration::with('feeRegistrationServices', 'feeRegistrationSectors')
        ->whereIn('company_id', $request['company_id'])
        ->where(function ($query) use ($request) {
            $this->applySearchFilter($query, $request);
        })
        ->orderBy('fee_registration.created_at','DESC')
        ->paginate(Config::get('services.paginate_row'));
    }

    /**
     * Show the fee registration with related fee registration services and sectors.
     * 
     * @param array $request The request data containing company id, fee registration ID
     * @return mixed Returns the fee registration with related fee registration services and sectors.
     */
    public function show($request) : mixed
    {
        return $this->feeRegistration::whereIn('company_id', $request['company_id'])->with('feeRegistrationServices', 'feeRegistrationSectors')->find($request['id']);
    }

	/**
     * Updates the fee registration from the given request data.
     * 
     * @param array $request The array containing fee registration data.
     * @return mixed Returns an mixed with the following keys:
     * - "isUpdated" (boolean): A value returns false if feeRegistration is null.
     * - "isUpdated" (boolean): Indicates whether the data was updated. Always set to `false`.
     */
    public function update($request): mixed
    {
        $data = $this->showFeeRegistration($request);
        if (is_null($data)) {
            return [
                "isUpdated" => false,
                "message"=> self::MESSAGE_DATA_NOT_FOUND
            ];
        }
        
        $user = $this->getJwtUserAuthenticate();
        $request['modified_by'] = $user['id'];
        $this->updateFeeRegistrationProcess($request);

        return  [
            "isUpdated" => $data->update($request->all()),
            "message" => self::MESSAGE_UPDATED_SUCCESSFULLY
        ];
    }

	/**
     * Delete the fee registration.
     * 
     * @param array $request The array containing fee registration id.
     * @return mixed Returns an mixed with the following keys:
     * - "isDeleted" (boolean): A value returns false if feeRegistration is null.
     * - "isDeleted" (boolean): Indicates whether the data was deleted. Always set to `false`.
     */  
    public function delete($request): mixed
    { 
        $data = showFeeRegistration($request);
        if (is_null($data)) {
            return [
                "isDeleted" => false,
                "message" => self::MESSAGE_DATA_NOT_FOUND
            ];
        }

        return [
            "isDeleted" => $data->delete(),
            "message" => self::MESSAGE_DELETED_SUCCESSFULLY
        ];
    }
    
    /**
     * Show the sectors.
     * 
     * @param array $request The request data containing company id
     * @return mixed Returns the sectors.
     */
    private function showCompanySectors($request)
    {
        return $this->sectors->where('company_id', $request['company_id'])
            ->select('id')
            ->get()
            ->toArray();
    }
    
    /**
     * Creates a new fee registration from the given request data.
     * 
     * @param array $request The array containing fee registration data.
     *                      The array should have the following keys:
     *                      - item_name: The item name of the fee.
     *                      - cost: The cost of the fee.
     *                      - fee_type: The fee type of the fee.
     *                      - company_id: The company id of the fee.
     *                      - created_by: The created fee created by.
     * 
     * @return feeRegistration The newly created fee object.
     */
    private function createFeeRegistration($request)
    {
        return $this->feeRegistration::create([
            'item_name' => $request["item_name"],
            'cost' => $request["cost"],
            'fee_type' => $request["fee_type"],
            'created_by' => $request["created_by"],
            'company_id' => $request['company_id']
        ]);
    }
    
    /**
     * Show the services.
     * 
     * @param int $serviceType The id of the services
     * @return mixed Returns the services.
     */
    private function showServices($serviceType)
    {
        return $this->services->where('id', '=', $serviceType)->select('id','service_name','status')->get();
    }
    
    /**
     * Creates a new fee reg services from the given request data.
     * 
     * @param int $feeRegistrationId The id of the fee reg services
     * @param array $request The array containing services data.
     *                      The array should have the following keys:
     *                      - "applicable_for": The array of applicable service type.
     *                      - fee_reg_id: The fee reg id of the service.
     *                      - service_id: The service id of the service.
     *                      - service_name: The service name of the service.
     *                      - status: The status of the service.
     * 
     * @return void.
     */
    private function createFeeRegServices($request, $feeRegistrationId)
    {
        foreach ($request['applicable_for'] as $serviceType) {
            $servicesData = $this->showServices($serviceType);
            foreach ($servicesData as $service) {
                $this->feeRegServices::create([
                    'fee_reg_id' => $feeRegistrationId,
                    'service_id' => $service->id,
                    'service_name' => $service->service_name,
                    'status' => $service->status,
                ]);
            }
        }
    }
    
    /**
     * Show the sectors.
     * 
     * @param int $sectorId The id of the sector
     * @return mixed Returns the sectors.
     */
    private function showSectors($sectorId)
    {
        return $this->sectors->where('id', '=', $sectorId)->select('id','sector_name','sub_sector_name', 'checklist_status')->get();
    }
    
    /**
     * Creates a new fee reg sectors from the given request data.
     * 
     * @param int $feeRegistrationId The id of the fee reg sectors
     * @param array $request The array containing sectors data.
     *                      The array should have the following keys:
     *                      - "sectors": The array of applicable sectors.
     *                      - fee_reg_id: The fee reg id of the sector.
     *                      - service_id: The service id of the sector.
     *                      - service_name: The service name of the sector.
     *                      - sub_sector_name: The sub sector name of the sector.
     *                      - checklist_status: The checklist status of the sector.
     * 
     * @return void.
     */
    private function createFeeRegSectors($request, $feeRegistrationId)
    {
        foreach ($request['sectors'] as $sectorId) {
            $sectorsData = $this->showSectors($sectorId);
            foreach ($sectorsData as $sector) {
                $this->feeRegSectors::create([
                    'fee_reg_id' => $feeRegistrationId,
                    'sector_id' => $sector->id,
                    'sector_name' => $sector->sector_name,
                    'sub_sector_name' => $sector->sub_sector_name,
                    'checklist_status' => $sector->checklist_status,
                ]);
            }
        }
    }
    
    /**
     * Apply search filter to the query.
     *
     * @param Illuminate\Database\Query\Builder $query The query builder instance
     * @param array $request The request data containing the search keyword.
     * 
     * @return void
     */
    private function applySearchFilter($query, $request)
    {
        if (!empty($request['search_param'])) {
            $query->where('item_name', 'like', '%' . $request['search_param'] . '%')
            ->orWhere('fee_type', 'like', '%' . $request['search_param'] . '%');
        }
        if (!empty($request['filter'])) {
            $query->where('fee_type', '=', $request['filter']);
        }
    }
    
    /**
     * Show the fee registration.
     * 
     * @param array $request The request data containing company id, fee registration id
     * @return mixed Returns the fee registration.
     */
    private function showFeeRegistration($request)
    {
        return $this->feeRegistration::where('company_id', $request['company_id'])->find($request['id']);
    }
    
    /**
     * Show the fee reg services.
     * 
     * @param array $request The request data containing company id, fee reg services id
     * @return mixed Returns the fee reg services.
     */
    private function showFeeRegServicesType($request)
    {
        return $this->feeRegServices->where('fee_reg_id', '=', $request['id'])->select('service_id', 'service_name')->get();
    }
    
    /**
     * Creates a new selected fee reg services from the given request data.
     * 
     * @param array $selectedDataToAdd The selected fee reg services
     * @param array $request The array containing selected services data.
     *                      The array should have the following keys:
     *                      - fee_reg_id: The fee reg id of the service.
     *                      - service_id: The service id of the service.
     *                      - service_name: The service name of the service.
     *                      - status: The status of the service.
     * 
     * @return void.
     */
    private function createSelectedFeeRegServices($selectedDataToAdd, $request)
    {
        if (!empty($selectedDataToAdd)) {
            foreach ($selectedDataToAdd as $serviceType) {
                $serviceTypeData = $this->showServices($serviceType);
                foreach ($serviceTypeData as $service) {
                    $this->feeRegServices::create([
                        'fee_reg_id' => $request['id'],
                        'service_id' => $service->id,
                        'service_name' => $service->service_name,
                        'status' => $service->status,
                    ]);
                }
            }
        }
    }
    
    /**
     * Delete the fee reg services
     * 
     * @param int $serviceType The id of the service.
     * @param array $request The array containing fee reg id.
     * 
     * @return void.
     */
    private function deleteFeeRegServices($serviceType, $request)
    {
        $this->feeRegServices::where('fee_reg_id', '=' ,$request['id'])->where('service_id', '=' ,$serviceType)->delete();
    }
    
    /**
     * Remove the selected fee reg services
     * 
     * @param array $selectedDataToRemove The id of the selected service type.
     * @param array $request The array containing fee reg id.
     * 
     * @return void.
     */
    private function removeSelectedFeeRegServices($selectedDataToRemove, $request)
    {
        if (!empty($selectedDataToRemove)) {
            foreach ($selectedDataToRemove as $serviceType) {
                $this->deleteFeeRegServices($serviceType, $request);           
            }            
        }
    }
    
    /**
     * Show the fee reg sectors.
     * 
     * @param array $request The request data containing fee reg id
     * @return mixed Returns the fee reg sectors.
     */
    private function showFeeRegSectorsType($request)
    {
        return $this->feeRegSectors->where('fee_reg_id', '=', $request['id'])->select('sector_id', 'sector_name')->get();
    }
    
    /**
     * Creates a new selected fee reg sectors from the given request data.
     * 
     * @param array $selectedSectorDataToAdd The selected fee reg sectors
     * @param array $request The array containing selected sectors data.
     *                      The array should have the following keys:
     *                      - fee_reg_id: The fee reg id of the sector.
     *                      - service_id: The service id of the sector.
     *                      - service_name: The service name of the sector.
     *                      - sub_sector_name: The sub sector name of the sector.
     *                      - checklist_status: The checklist status of the sector.
     * 
     * @return void.
     */
    private function createSelectedFeeRegSectors($selectedSectorDataToAdd, $request)
    {
        if (!empty($selectedSectorDataToAdd)) {
            foreach ($selectedSectorDataToAdd as $sectorId) {
                $sectorData = $this->showSectors($sectorId);
                foreach ($sectorData as $sector) {
                    $this->feeRegSectors::create([
                        'fee_reg_id' => $request['id'],
                        'sector_id' => $sector->id,
                        'sector_name' => $sector->sector_name,
                        'sub_sector_name' => $sector->sub_sector_name,
                        'checklist_status' => $sector->checklist_status,
                    ]);
                }
            }
        }
    }
    
    /**
     * Delete the fee reg sector
     * 
     * @param int $sectorId The id of the sector.
     * @param array $request The array containing fee reg id.
     * 
     * @return void.
     */
    private function deleteFeeRegSectors($sectorId, $request)
    {
        $this->feeRegSectors::where('fee_reg_id', '=' ,$request['id'])
            ->where('sector_id', '=' ,$sectorId)->delete();
    }
    
    /**
     * Remove the selected fee reg sector
     * 
     * @param array $selectedSectorDataToRemove The selected sector.
     * @param array $request The array containing sector id.
     * 
     * @return void.
     */
    private function removeSelectedFeeRegSectors($selectedSectorDataToRemove, $request)
    {
        if (!empty($selectedSectorDataToRemove)) {
            foreach ($selectedSectorDataToRemove as $sectorId) {
                $this->deleteFeeRegSectors($sectorId, $request);           
            }            
        }
    }
    
    /**
     * Updates the fee registration process.
     * 
     * @param array $request The array containing fee registration data.
     *                      The array should have the following keys:
     *                      - fee_reg_id, service_id, service_name, sub_sector_name, 
     *                        checklist_status, applicable_for, fee_type, sectors
     * 
     * @return void.
     */
    private function updateFeeRegistrationProcess($request)
    {
        if (strtolower($request["fee_type"]) != self::FEE_TYPE_STANDARD) {
            $feeRegServicesType = $this->showFeeRegServicesType($request);
            $feeRegServicesTypeData = [];
            foreach ($feeRegServicesType as $serviceType) {
                $feeRegServicesTypeData[] = $serviceType->service_id;
            }
            $selectedDataToAdd = array_diff($request['applicable_for'], $feeRegServicesTypeData);
            $selectedDataToRemove = array_diff($feeRegServicesTypeData, $request['applicable_for']);
            $this->createSelectedFeeRegServices($selectedDataToAdd, $request);
            $this->removeSelectedFeeRegServices($selectedDataToRemove, $request);
            
            $feeRegSectorsType = $this->showFeeRegSectorsType($request);
            $feeRegSectorsTypeData = [];
            foreach ($feeRegSectorsType as $sector) {
                $feeRegSectorsTypeData[] = $sector->sector_id;
            }
            $selectedSectorDataToAdd = array_diff($request['sectors'], $feeRegSectorsTypeData);
            $selectedSectorDataToRemove = array_diff($feeRegSectorsTypeData, $request['sectors']);
            $this->createSelectedFeeRegSectors($selectedSectorDataToAdd, $request);
            $this->removeSelectedFeeRegSectors($selectedSectorDataToRemove, $request);
        }
    }

    /**
     * get the user of jwt authenticate.
     *
     * @return mixed Returns the user data.
     */
    private function getJwtUserAuthenticate(): mixed
    {
        return JWTAuth::parseToken()->authenticate();
    }
}