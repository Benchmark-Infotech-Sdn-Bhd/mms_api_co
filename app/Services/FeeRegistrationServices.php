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
     * @param $request
     * @return mixed | void
     */
    public function inputValidation($request)
    {
        if (!($this->feeRegistration->validate($request->all()))) {
            return $this->feeRegistration->errors();
        }
    }

    /**
     * @param $request
     * @return mixed | void
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
     *
     * @param $request
     * @return mixed 
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
     *
     * @param $request
     * @return LengthAwarePaginator
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
     *
     * @param $request
     * @return mixed
     */
    public function show($request) : mixed
    {
        return $this->feeRegistration::whereIn('company_id', $request['company_id'])->with('feeRegistrationServices', 'feeRegistrationSectors')->find($request['id']);
    }

	/**
     *
     * @param $request
     * @return mixed
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
     *
     * @param $request
     * @return mixed
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

    private function showCompanySectors($request)
    {
        return $this->sectors->where('company_id', $request['company_id'])
            ->select('id')
            ->get()
            ->toArray();
    }

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

    private function showServices($serviceType)
    {
        return $this->services->where('id', '=', $serviceType)->select('id','service_name','status')->get();
    }

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

    private function showSectors($sectorId)
    {
        return $this->sectors->where('id', '=', $sectorId)->select('id','sector_name','sub_sector_name', 'checklist_status')->get();
    }

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

    private function showFeeRegistration($request)
    {
        return $this->feeRegistration::where('company_id', $request['company_id'])->find($request['id']);
    }

    private function showFeeRegServicesType($request)
    {
        return $this->feeRegServices->where('fee_reg_id', '=', $request['id'])->select('service_id', 'service_name')->get();
    }

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

    private function deleteFeeRegServices($serviceType, $request)
    {
        $this->feeRegServices::where('fee_reg_id', '=' ,$request['id'])->where('service_id', '=' ,$serviceType)->delete();
    }

    private function removeSelectedFeeRegServices($selectedDataToRemove, $request)
    {
        if (!empty($selectedDataToRemove)) {
            foreach ($selectedDataToRemove as $serviceType) {
                $this->deleteFeeRegServices($serviceType, $request);           
            }            
        }
    }

    private function showFeeRegSectorsType($request)
    {
        return $this->feeRegSectors->where('fee_reg_id', '=', $request['id'])->select('sector_id', 'sector_name')->get();
    }

    private function createSelectedFeeRegSectors($selectedSectorDataToAdd, $request)
    {
        if (!empty($selectedSectorDataToAdd)) {
            foreach ($selectedSectorDataToAdd as $sectorId) {
                $sectorData = showSectors($sectorId)
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

    private function deleteFeeRegSectors($sectorId, $request)
    {
        $this->feeRegSectors::where('fee_reg_id', '=' ,$request['id'])
            ->where('sector_id', '=' ,$sectorId)->delete();
    }

    private function removeSelectedFeeRegSectors($selectedSectorDataToRemove, $request)
    {
        if (!empty($selectedSectorDataToRemove)) {
            foreach ($selectedSectorDataToRemove as $sectorId) {
                $this->deleteFeeRegSectors($sectorId, $request);           
            }            
        }
    }

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