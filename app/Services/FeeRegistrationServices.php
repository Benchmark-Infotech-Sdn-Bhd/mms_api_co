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
    /**
     * @var feeRegistration
     */
    private FeeRegistration $feeRegistration;
    /**
     * @var services
     */
    private Services $services;
    /**
     * @var feeRegServices
     */
    private FeeRegServices $feeRegServices;
    /**
     * @var feeRegSectors
     */
    private FeeRegSectors $feeRegSectors;
    /**
     * @var sectors
     */
    private Sectors $sectors;
    public function __construct(Sectors $sectors, FeeRegServices $feeRegServices, FeeRegSectors $feeRegSectors, Services $services, FeeRegistration $feeRegistration)
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
        if(!($this->feeRegistration->validate($request->all()))){
            return $this->feeRegistration->errors();
        }
    }
    /**
     * @param $request
     * @return mixed | void
     */
    public function updateValidation($request)
    {
        if(strtolower($request["fee_type"]) == 'standard'){            
            if(!($this->feeRegistration->validateStandardUpdation($request->all()))){
                return $this->feeRegistration->errors();
            }
        }
        else{
            if(!($this->feeRegistration->validateUpdation($request->all()))){
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
        $user = JWTAuth::parseToken()->authenticate();
        $request['created_by'] = $user['id'];
        $feeRegistrationData = $this->feeRegistration::create([
            'item_name' => $request["item_name"],
            'cost' => $request["cost"],
            'fee_type' => $request["fee_type"],
            'created_by' => $request["created_by"],
            'company_id' => $user['company_id']
        ]);
        $feeRegistrationId = $feeRegistrationData->id;
        foreach ($request['applicable_for'] as $serviceType) {
            $servicesData = $this->services->where('id', '=', $serviceType)->select('id','service_name','status')->get();
            foreach ($servicesData as $service) {
                $this->feeRegServices::create([
                    'fee_reg_id' => $feeRegistrationId,
                    'service_id' => $service->id,
                    'service_name' => $service->service_name,
                    'status' => $service->status,
                ]);
            }
        }
        foreach ($request['sectors'] as $sectorId) {
            $sectorsData = $this->sectors->where('id', '=', $sectorId)->select('id','sector_name','sub_sector_name', 'checklist_status')->get();
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
        return $feeRegistrationData;
    }
    /**
     *
     * @param $request
     * @return LengthAwarePaginator
     */
    public function list($request)
    {
        return $this->feeRegistration::with(['feeRegistrationServices', 'feeRegistrationSectors', 'company' => function ($query) {
            $query->select(['id', 'company_name']);
        }])
        ->whereIn('company_id', $request['company_id'])
        ->where(function ($query) use ($request) {
            if (isset($request['search_param']) && !empty($request['search_param'])) {
                $query->where('item_name', 'like', '%' . $request['search_param'] . '%')
                ->orWhere('fee_type', 'like', '%' . $request['search_param'] . '%');
            }
            if (isset($request['filter']) && !empty($request['filter'])) {
                $query->where('fee_type', '=', $request->filter);
            }
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
        return $this->feeRegistration::with('feeRegistrationServices', 'feeRegistrationSectors')->find($request['id']);
    }
	 /**
     *
     * @param $request
     * @return mixed
     */
    public function update($request): mixed
    {
        $data = $this->feeRegistration::findorfail($request['id']);
        $user = JWTAuth::parseToken()->authenticate();
        $request['modified_by'] = $user['id'];
        if(strtolower($request["fee_type"]) != 'standard'){
            $feeRegServicesType = $this->feeRegServices->where('fee_reg_id', '=', $request['id'])->select('service_id', 'service_name')->get();
            $feeRegServicesTypeData = [];
            foreach ($feeRegServicesType as $serviceType) {
                $feeRegServicesTypeData[] = $serviceType->service_id;
            }
            $selectedDataToAdd = array_diff($request['applicable_for'], $feeRegServicesTypeData);
            $selectedDataToRemove = array_diff($feeRegServicesTypeData, $request['applicable_for']);
            if (!empty($selectedDataToAdd)) {
                foreach ($selectedDataToAdd as $serviceType) {
                    $serviceTypeData = $this->services->where('id', '=', $serviceType)->select('id','service_name','status')->get();
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
            if (!empty($selectedDataToRemove)) {
                foreach ($selectedDataToRemove as $serviceType) {
                    $this->feeRegServices::where('fee_reg_id', '=' ,$request['id'])->where('service_id', '=' ,$serviceType)->delete();           
                }            
            }
            $feeRegSectorsType = $this->feeRegSectors->where('fee_reg_id', '=', $request['id'])->select('sector_id', 'sector_name')->get();
            $feeRegSectorsTypeData = [];
            foreach ($feeRegSectorsType as $sector) {
                $feeRegSectorsTypeData[] = $sector->sector_id;
            }
            $selectedSectorDataToAdd = array_diff($request['sectors'], $feeRegSectorsTypeData);
            $selectedSectorDataToRemove = array_diff($feeRegSectorsTypeData, $request['sectors']);
            if (!empty($selectedSectorDataToAdd)) {
                foreach ($selectedSectorDataToAdd as $sectorId) {
                    $sectorData = $this->sectors->where('id', '=', $sectorId)->select('id','sector_name','sub_sector_name', 'checklist_status')->get();
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
            if (!empty($selectedSectorDataToRemove)) {
                foreach ($selectedSectorDataToRemove as $sectorId) {
                    $this->feeRegSectors::where('fee_reg_id', '=' ,$request['id'])->where('sector_id', '=' ,$sectorId)->delete();           
                }            
            }
        }
        return  [
            "isUpdated" => $data->update($request->all()),
            "message" => "Updated Successfully"
        ];
    }
	 /**
     *
     * @param $request
     * @return mixed
     */    
    public function delete($request): mixed
    {     
        $data = $this->feeRegistration::find($request['id']);
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