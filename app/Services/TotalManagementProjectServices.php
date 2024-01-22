<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Config;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\TotalManagementProject;

class TotalManagementProjectServices
{
    private TotalManagementProject $totalManagementProject;
    /**
     * TotalManagementProjectServices constructor.
     * 
     * @param TotalManagementProject $totalManagementProject
     */
    public function __construct(TotalManagementProject $totalManagementProject)
    {
        $this->totalManagementProject = $totalManagementProject;
    }
    /**
     * validate the add project request data
     * 
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
            'supervisor_id' => 'required',
            'supervisor_type' => 'required',
            /* 'employee_id' => 'required', */
            'transportation_provider_id' => 'required',
            'driver_id' => 'required',
            'annual_leave' => 'required|regex:/^[0-9]+$/|max:2',
            'medical_leave' => 'required|regex:/^[0-9]+$/|max:2',
            'hospitalization_leave' => 'required|regex:/^[0-9]+$/|max:2'
        ];
    }
    /**
     * validate the update project request data
     * 
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
            'supervisor_id' => 'required',
            'supervisor_type' => 'required',
            /* 'employee_id' => 'required', */
            'transportation_provider_id' => 'required',
            'driver_id' => 'required',
            'annual_leave' => 'required|regex:/^[0-9]+$/|max:2',
            'medical_leave' => 'required|regex:/^[0-9]+$/|max:2',
            'hospitalization_leave' => 'required|regex:/^[0-9]+$/|max:2'
        ];
    }
    /**
     * list the total management projects
     * 
     * @param $request
     * @return mixed
     */   
    public function list($request): mixed
    {
        return $this->totalManagementProject
        //->leftJoin('employee', 'employee.id', '=', 'total_management_project.employee_id')
        ->leftJoin('users', 'users.id', '=', 'total_management_project.supervisor_id')
        ->leftJoin('employee', 'employee.id', '=', 'users.reference_id')
        ->leftJoin('transportation as supervisorTransportation', 'supervisorTransportation.id', '=', 'users.reference_id')
        ->leftJoin('vendors', 'vendors.id', '=', 'total_management_project.transportation_provider_id')
        ->leftJoin('transportation', 'transportation.id', '=', 'total_management_project.driver_id')
        ->leftJoin('worker_employment', function($query) {
            $query->on('worker_employment.project_id','=','total_management_project.id')
            ->where('worker_employment.service_type', 'Total Management')
            ->where('worker_employment.transfer_flag', 0)
            ->whereNull('worker_employment.remove_date');
        })
        ->leftJoin('workers', function($query) {
            $query->on('workers.id','=','worker_employment.worker_id')
            ->whereIN('workers.total_management_status', Config::get('services.TOTAL_MANAGEMENT_WORKER_STATUS'));
        })
        ->where('total_management_project.application_id',$request['application_id'])
        ->where(function ($query) use ($request) {
            $search = $request['search'] ?? '';
            if(!empty($search)) {
                $query->where('total_management_project.name', 'like', '%'.$search.'%')
                ->orWhere('total_management_project.state', 'like', '%'.$search.'%')
                ->orWhere('total_management_project.city', 'like', '%'.$search.'%')
                ->orWhere('employee.employee_name', 'like', '%'.$search.'%');
            }
        })
        ->select('total_management_project.id', 'total_management_project.application_id', 'total_management_project.name', 'total_management_project.state', 'total_management_project.city', 'total_management_project.address', 'total_management_project.supervisor_id', 'total_management_project.supervisor_type', 'total_management_project.employee_id', 'employee.employee_name', 'total_management_project.transportation_provider_id', 'vendors.name as vendor_name', 'total_management_project.driver_id', 'transportation.driver_name', 'total_management_project.assign_as_supervisor', 'total_management_project.annual_leave', 'total_management_project.medical_leave', 'total_management_project.hospitalization_leave', 'total_management_project.created_at', 'total_management_project.updated_at')
        ->selectRaw('count(distinct workers.id) as workers, count(distinct worker_employment.id) as worker_employments, IF(total_management_project.supervisor_type = "employee", employee.employee_name, supervisorTransportation.driver_name) as supervisor_name')
        ->groupBy('total_management_project.id', 'total_management_project.application_id', 'total_management_project.name', 'total_management_project.state', 'total_management_project.city', 'total_management_project.address', 'total_management_project.supervisor_id', 'total_management_project.supervisor_type', 'total_management_project.employee_id', 'employee.employee_name', 'total_management_project.transportation_provider_id', 'vendors.name', 'total_management_project.driver_id', 'transportation.driver_name', 'total_management_project.assign_as_supervisor', 'total_management_project.annual_leave', 'total_management_project.medical_leave', 'total_management_project.hospitalization_leave', 'total_management_project.created_at', 'total_management_project.updated_at', 'supervisorTransportation.driver_name')
        ->orderBy('total_management_project.id', 'desc')
        ->paginate(Config::get('services.paginate_row'));
    }
    /**
     * show the total management project detail
     * 
     * @param $request
     *   - id (int) The ID of the project
     * 
     * @return mixed
     */   
    public function show($request): mixed
    {
        return $this->totalManagementProject
        ->join('total_management_applications', function ($join) use ($request) {
            $join->on('total_management_applications.id', '=', 'total_management_project.application_id')
                 ->whereIn('total_management_applications.company_id', $request['company_id']);
        })
        ->select('total_management_project.id', 'total_management_project.application_id', 'total_management_project.name', 'total_management_project.state', 'total_management_project.city', 'total_management_project.address', 'total_management_project.supervisor_id','total_management_project.supervisor_type', 'total_management_project.employee_id','total_management_project.transportation_provider_id', 'total_management_project.driver_id','total_management_project.assign_as_supervisor', 'total_management_project.annual_leave','total_management_project.medical_leave', 'total_management_project.hospitalization_leave','total_management_project.created_by', 'total_management_project.modified_by','total_management_project.created_at', 'total_management_project.updated_at','total_management_project.deleted_at')
        ->find($request['id']);
    }
    /**
     * add a total management project 
     * 
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
        $this->createTotalMangementProject($request);
        return true;
    }
    /**
     * create a new entry for total management project.
     *
     * @param array $request
     * @return void
     */
    private function createTotalMangementProject($request)
    {
        $this->totalManagementProject->create([
            'application_id' => $request['application_id'] ?? 0,
            'name' => $request['name'] ?? '',
            'state' => $request['state'] ?? '',
            'city' => $request['city'] ?? '',
            'address' => $request['address'] ?? '',
            'supervisor_id' => $request['supervisor_id'] ?? 0,
            'supervisor_type' => $request['supervisor_type'] ?? 0,
            //'employee_id' => $request['employee_id'] ?? 0,
            'transportation_provider_id' => $request['transportation_provider_id'] ?? 0,
            'driver_id' => $request['driver_id'] ?? 0,
            //'assign_as_supervisor' => $request['assign_as_supervisor'] ?? 0,
            'annual_leave' => $request['annual_leave'] ?? 0,
            'medical_leave' => $request['medical_leave'] ?? 0,
            'hospitalization_leave' => $request['hospitalization_leave'] ?? 0,
            'created_by' => $request['created_by'] ?? 0,
            'modified_by' => $request['created_by'] ?? 0
        ]);
        
    }
    /**
     * update the total management project
     * 
     * @param $request
     *        id (int) total management project id
     *        request total mangement project data
     * 
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
        $totalManagementProject = $this->getTotalManagementProject($request);
        $this->updateTotalManagementProject($totalManagementProject, $request);
        return true;
    }
    /**
     * Retrieve totalmanagement project record.
     *
     * @param array $request
     * @return mixed
     */
    private function getTotalManagementProject($request)
    {
        return $this->totalManagementProject
        ->join('total_management_applications', function ($join) use ($request) {
            $join->on('total_management_applications.id', '=', 'total_management_project.application_id')
                 ->whereIn('total_management_applications.company_id', $request['company_id']);
        })->select('total_management_project.id', 'total_management_project.application_id', 'total_management_project.name', 'total_management_project.state', 'total_management_project.city', 'total_management_project.address', 'total_management_project.supervisor_id', 'total_management_project.supervisor_type', 'total_management_project.employee_id', 'total_management_project.transportation_provider_id', 'total_management_project.driver_id', 'total_management_project.assign_as_supervisor', 'total_management_project.annual_leave', 'total_management_project.medical_leave', 'total_management_project.hospitalization_leave', 'total_management_project.created_by', 'total_management_project.modified_by', 'total_management_project.created_at', 'total_management_project.updated_at', 'total_management_project.deleted_at')
        ->find($request['id']);
    }

    /**
     * Update totalmanagement project based on the provided request.
     *
     * @param mixed $totalManagementProject
     * @param $request
     * @return void
     */
    private function updateTotalManagementProject($totalManagementProject, $request)
    {
        $totalManagementProject->name =  $request['name'] ?? $totalManagementProject->name;
        $totalManagementProject->state =  $request['state'] ?? $totalManagementProject->state;
        $totalManagementProject->city =  $request['city'] ?? $totalManagementProject->city;
        $totalManagementProject->address =  $request['address'] ?? $totalManagementProject->address;
        $totalManagementProject->supervisor_id =  $request['supervisor_id'] ?? $totalManagementProject->supervisor_id;
        $totalManagementProject->supervisor_type =  $request['supervisor_type'] ?? $totalManagementProject->supervisor_type;
        //$totalManagementProject->employee_id =  $request['employee_id'] ?? $totalManagementProject->employee_id;
        $totalManagementProject->transportation_provider_id =  $request['transportation_provider_id'] ?? $totalManagementProject->transportation_provider_id;
        $totalManagementProject->driver_id =  $request['driver_id'] ?? $totalManagementProject->driver_id;
        //$totalManagementProject->assign_as_supervisor =  $request['assign_as_supervisor'] ?? $totalManagementProject->assign_as_supervisor;
        $totalManagementProject->annual_leave =  $request['annual_leave'] ?? $totalManagementProject->annual_leave;
        $totalManagementProject->medical_leave =  $request['medical_leave'] ?? $totalManagementProject->medical_leave;
        $totalManagementProject->hospitalization_leave =  $request['hospitalization_leave'] ?? $totalManagementProject->hospitalization_leave;
        $totalManagementProject->modified_by =  $request['modified_by'] ?? $totalManagementProject->modified_by;
        $totalManagementProject->save();
    }
}