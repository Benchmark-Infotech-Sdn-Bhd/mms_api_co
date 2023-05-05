<?php


namespace App\Services;

use App\Models\Branch;
use App\Models\Services;
use App\Models\State;
use App\Models\BranchesServices;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Config;
use Tymon\JWTAuth\Facades\JWTAuth;

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
    /**
     * @var state
     */
    private State $state;

    public function __construct(Branch $branch,Services $services,BranchesServices $branchesServices, State $state)
    {
        $this->branch = $branch;
        $this->services = $services;
        $this->branchesServices = $branchesServices;
        $this->state = $state;
    }
    /**
     * @param $request
     * @return mixed | boolean
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
     * @return mixed | boolean
     */
    public function updateValidation($request)
    {
        if(!($this->branch->validateUpdation($request->all()))){
            return $this->branch->errors();
        }
        return false;
    }
    
    /**
     * @param $request
     * @return mixed | boolean
     */
    public function updateStatusValidation($request,$rules)
    {
        if(!($this->branch->validateStatus($request,$rules))){
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
        $user = JWTAuth::parseToken()->authenticate();
        $request['created_by'] = $user['id'];
        $branchData = $this->branch::create([
            'branch_name' => $request["branch_name"],
            'state' => $request["state"],
            'city' => $request["city"],
            'branch_address' => $request["branch_address"],
            'postcode' => $request["postcode"],
            'remarks' => $request["remarks"],
            'created_by' => $request["created_by"],
        ]);
        $branchDataId = $branchData->id;
        foreach ($request['service_type'] as $serviceType) {
            $serviceTypeData = $this->services->where('id', '=', $serviceType)->select('id','service_name','status')->get();
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
     * @param $request
     * @return LengthAwarePaginator
     */ 
    public function list($request)
    {
        return $this->branch::with('branchServices')
        ->where(function ($query) use ($request) {
            if (isset($request['search_param']) && !empty($request['search_param'])) {
                $query->where('branch_name', 'like', '%' . $request['search_param'] . '%')
                ->orWhere('state', 'like', '%' . $request['search_param'] . '%')
                ->orWhere('city', 'like', '%' . $request['search_param'] . '%');
            }
        })
        ->orderBy('branch.created_at','DESC')
        ->paginate(Config::get('services.paginate_row'));
    }
	 /**
     *
     * @param $request
     * @return mixed
     */
    public function show($request) : mixed
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
        $user = JWTAuth::parseToken()->authenticate();
        $request['modified_by'] = $user['id'];
        if(is_null($data)){
            return [
                "isUpdated" => false,
                "message" => "Data not found"
            ];
        }
        $branchesServiceType = $this->branchesServices->where('branch_id', '=', $request['id'])->select('service_id', 'service_name')->get();
        $branchesServiceTypeData = [];
        foreach ($branchesServiceType as $serviceType) {
            $branchesServiceTypeData[] = $serviceType->service_id;
        }
        $selectedDataToAdd = array_diff($request['service_type'], $branchesServiceTypeData);
        $selectedDataToRemove = array_diff($branchesServiceTypeData, $request['service_type']);
        if (!empty($selectedDataToAdd)) {
            foreach ($selectedDataToAdd as $serviceType) {
                $serviceTypeData = $this->services->where('id', '=', $serviceType)->select('id','service_name','status')->get();
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
                $this->branchesServices::where('branch_id', '=' ,$request['id'])->where('service_id', '=' ,$serviceType)->delete();           
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
        $data->branchServices()->delete();
        $data->delete();
        return [
            "isDeleted" => true,
            "message" => "Deleted Successfully"
        ];
    }
    /**
     * @return mixed
     */
    public function dropDown(): mixed
    {
        return $this->branch::where('status', '=' ,1)->select('id','branch_name')->orderBy('branch.created_at','DESC')->get();
    }

    /**
     * @param $request
     * @return array
     */
    public function updateStatus($request) : array
    {
        $branch = $this->branch->find($request['id']);
        if(is_null($branch)){
            return [
                "isUpdated" => false,
                "message"=> "Data not found"
            ];
        }
        $branch->status = $request['status'];
        return  [
            "isUpdated" => $branch->save(),
            "message" => "Updated Successfully"
        ];
    }
}