<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Config;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\TotalManagementProject;

class TotalManagementProjectServices
{
    /**
     * @var TotalManagementProject
     */
    private TotalManagementProject $totalManagementProject;
    /**
     * TotalManagementProjectServices constructor.
     * @param TotalManagementProject $totalManagementProject
     */
    public function __construct(TotalManagementProject $totalManagementProject)
    {
        $this->totalManagementProject = $totalManagementProject;
    }
    /**
     * @return array
     */
    public function addValidation(): array
    {
        return [
            'application_id' => 'required',
            'name' => 'required',
            'state' => 'required',
            'city' => 'required',
            'address' => 'required',
            'employee_id' => 'required',
            'transportation_provider_id' => 'required',
            'driver_id' => 'required',
            'annual_leave' => 'required|regex:/^[0-9]+$/|max:2',
            'medical_leave' => 'required|regex:/^[0-9]+$/|max:2',
            'hospitalization_leave' => 'required|regex:/^[0-9]+$/|max:2'
        ];
    }
    /**
     * @return array
     */
    public function updateValidation(): array
    {
        return [
            'id' => 'required',
            'name' => 'required',
            'state' => 'required',
            'city' => 'required',
            'address' => 'required',
            'employee_id' => 'required',
            'transportation_provider_id' => 'required',
            'driver_id' => 'required',
            'annual_leave' => 'required|regex:/^[0-9]+$/|max:2',
            'medical_leave' => 'required|regex:/^[0-9]+$/|max:2',
            'hospitalization_leave' => 'required|regex:/^[0-9]+$/|max:2'
        ];
    }
    /**
     * @param $request
     * @return mixed
     */   
    public function list($request): mixed
    {
        return $this->totalManagementProject
        ->leftJoin('employee', 'employee.id', '=', 'total_management_project.employee_id')
        ->leftJoin('vendors', 'vendors.id', '=', 'total_management_project.transportation_provider_id')
        ->leftJoin('transportation', 'transportation.id', '=', 'total_management_project.driver_id')
        ->where('total_management_project.application_id',$request['application_id'])
        ->where(function ($query) use ($request) {
            if(isset($request['search']) && !empty($request['search'])) {
                $query->where('total_management_project.name', 'like', '%'.$request['search'].'%')
                ->orWhere('total_management_project.state', 'like', '%'.$request['search'].'%')
                ->orWhere('total_management_project.city', 'like', '%'.$request['search'].'%')
                ->orWhere('employee.employee_name', 'like', '%'.$request['search'].'%');
            }
        })
        ->select('total_management_project.id', 'total_management_project.application_id', 'total_management_project.name', 'total_management_project.state', 'total_management_project.city', 'total_management_project.address', 'total_management_project.employee_id', 'employee.employee_name', 'total_management_project.transportation_provider_id', 'vendors.name as vendor_name', 'total_management_project.driver_id', 'transportation.driver_name', 'total_management_project.assign_as_supervisor', 'total_management_project.annual_leave', 'total_management_project.medical_leave', 'total_management_project.hospitalization_leave', 'total_management_project.created_at', 'total_management_project.updated_at')
        ->distinct('total_management_project.id')
        ->orderBy('total_management_project.id', 'desc')
        ->paginate(Config::get('services.paginate_row'));
    }
    /**
     * @param $request
     * @return mixed
     */   
    public function show($request): mixed
    {
        return $this->totalManagementProject->find($request['id']);
    }
    /**
     * @param $request
     * @return bool|array
     */   
    public function add($request): bool|array
    {
        $validator = Validator::make($request, $this->addValidation());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }
        $this->totalManagementProject->create([
            'application_id' => $request['application_id'] ?? 0,
            'name' => $request['name'] ?? '',
            'state' => $request['state'] ?? '',
            'city' => $request['city'] ?? '',
            'address' => $request['address'] ?? '',
            'employee_id' => $request['employee_id'] ?? 0,
            'transportation_provider_id' => $request['transportation_provider_id'] ?? 0,
            'driver_id' => $request['driver_id'] ?? 0,
            'assign_as_supervisor' => $request['assign_as_supervisor'] ?? 0,
            'annual_leave' => $request['annual_leave'] ?? 0,
            'medical_leave' => $request['medical_leave'] ?? 0,
            'hospitalization_leave' => $request['hospitalization_leave'] ?? 0,
            'created_by' => $request['created_by'] ?? 0,
            'modified_by' => $request['created_by'] ?? 0
        ]);
        return true;
    }
    /**
     * @param $request
     * @return bool|array
     */
    public function update($request): bool|array
    {
        $validator = Validator::make($request, $this->updateValidation());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }

        $totalManagementProject = $this->totalManagementProject->findOrFail($request['id']);
        
        $totalManagementProject->name =  $request['name'] ?? $totalManagementProject->name;
        $totalManagementProject->state =  $request['state'] ?? $totalManagementProject->state;
        $totalManagementProject->city =  $request['city'] ?? $totalManagementProject->city;
        $totalManagementProject->address =  $request['address'] ?? $totalManagementProject->address;
        $totalManagementProject->employee_id =  $request['employee_id'] ?? $totalManagementProject->employee_id;
        $totalManagementProject->transportation_provider_id =  $request['transportation_provider_id'] ?? $totalManagementProject->transportation_provider_id;
        $totalManagementProject->driver_id =  $request['driver_id'] ?? $totalManagementProject->driver_id;
        $totalManagementProject->assign_as_supervisor =  $request['assign_as_supervisor'] ?? $totalManagementProject->assign_as_supervisor;
        $totalManagementProject->annual_leave =  $request['annual_leave'] ?? $totalManagementProject->annual_leave;
        $totalManagementProject->medical_leave =  $request['medical_leave'] ?? $totalManagementProject->medical_leave;
        $totalManagementProject->hospitalization_leave =  $request['hospitalization_leave'] ?? $totalManagementProject->hospitalization_leave;
        $totalManagementProject->modified_by =  $request['modified_by'] ?? $totalManagementProject->modified_by;
        $totalManagementProject->save();

        return true;
    }
}