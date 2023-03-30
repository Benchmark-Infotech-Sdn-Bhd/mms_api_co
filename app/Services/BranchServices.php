<?php


namespace App\Services;

use App\Models\Branch;
use App\Models\Services;
use App\Models\BranchesServices;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class BranchServices
{
    /**
     * @var branch
     */
    private Branch $branch;
    /**
     * @var services
     */
    private Services $services;
    /**
     * @var branchesServices
     */
    private BranchesServices $branchesServices;

    public function __construct(Branch $branch,Services $services,BranchesServices $branchesServices)
    {
        $this->branch = $branch;
        $this->services = $services;
        $this->branchesServices = $branchesServices;
    }
    /**
     * @param $request
     * @return mixed
     */
    public function inputValidation($request)
    {
        if(!($this->branch->validate($request->all()))){
            return $this->branch->errors();
        }
        return false;
    }
    /**
     * @param $request
     * @return mixed
     */
    public function updateValidation($request)
    {
        if(!($this->branch->validateUpdation($request->all()))){
            return $this->branch->errors();
        }
        return false;
    }
	 /**
     *
     * @param $request
     * @return mixed
     */
    public function create($request): mixed
    {   
        $branchData = $this->branch::create([
            'branch_name' => $request["branch_name"],
            'state' => $request["state"],
            'city' => $request["city"],
            'branch_address' => $request["branch_address"],
            'postcode' => $request["postcode"],
            'remarks' => $request["remarks"],
        ]);
        $branchDataId = $branchData->id;
        foreach ($request['service_type'] as $serviceType) {
            $serviceTypeData = $this->services->where('service_name', '=', $serviceType)->get();
            foreach ($serviceTypeData as $service) {
                $this->branchesServices::create([
                    'branch_id' => $branchDataId,
                    'service_id' => $service->id,
                    'service_name' => $service->service_name,
                    'status' => $service->status,
                ]);
            }
        }
        return $branchData;
    }
	 /**
     *
     * @return LengthAwarePaginator
     */ 
    public function retrieveAll()
    {
        return $this->branch::with('branchServices')->orderBy('branch.created_at','DESC')->paginate(10);
    }
	 /**
     *
     * @param $request
     * @return mixed
     */
    public function retrieve($request) : mixed
    {
        return $this->branch::with('branchServices')->find($request['id']);
    }
	 /**
     *
     * @param $request
     * @return array
     */
    public function update($request): array
    {           
        $data = $this->branch::find($request['id']);
        if(is_null($data)){
            return [
                "isUpdated" => false,
                "message" => "Data not found"
            ];
        }
        $branchesServiceType = $this->branchesServices->where('branch_id', '=', $request['id'])->get();
        $branchesServiceTypeData = [];
        foreach ($branchesServiceType as $serviceType) {
            array_push($branchesServiceTypeData, $serviceType->service_name);
        }
        $selectedDataToAdd = array_diff($request['service_type'], $branchesServiceTypeData);
        $selectedDataToRemove = array_diff($branchesServiceTypeData, $request['service_type']);
        if (!empty($selectedDataToAdd)) {
            foreach ($selectedDataToAdd as $serviceType) {
                $serviceTypeData = $this->services->where('service_name', '=', $serviceType)->get();
                foreach ($serviceTypeData as $service) {
                    $this->branchesServices::create([
                        'branch_id' => $request['id'],
                        'service_id' => $service->id,
                        'service_name' => $service->service_name,
                        'status' => $service->status,
                    ]);
                }
            }
        }
        if (!empty($selectedDataToRemove)) {
            foreach ($selectedDataToRemove as $serviceType) {
                $this->branchesServices::where('branch_id', '=' ,$request['id'])->where('service_name', '=' ,$serviceType)->delete();           
            }            
        }
        return [
            "isUpdated" => $data->update($request->all()),
            "message" => "Updated Successfully"
        ];
    }
	 /**
     *
     * @param $request
     * @return array
     */    
    public function delete($request) : array
    {     
        $data = $this->branch::find($request['id']);
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
    /**
     *
     * @param $request
     * @return LengthAwarePaginator
     */
    public function search($request)
    {
        return $this->branch->where('branch_name', 'like', '%' . $request->search . '%')
        ->orWhere('state', 'like', '%' . $request->search . '%')
        ->orWhere('city', 'like', '%' . $request->search . '%')
        ->orderBy('branch.created_at','DESC')
        ->paginate(10);
    }
}