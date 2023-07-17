<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Config;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\DB;
use App\Models\TotalManagementProject;

class TotalManagementSupervisorServices
{
    /**
     * @var TotalManagementProject
     */
    private TotalManagementProject $totalManagementProject;
    /**
     * TotalManagementSupervisorServices constructor.
     * @param TotalManagementProject $totalManagementProject
     */
    public function __construct(TotalManagementProject $totalManagementProject)
    {
        $this->totalManagementProject = $totalManagementProject;
    }
    /**
     * @param $request
     * @return mixed
     */   
    public function list($request): mixed
    {
        return $this->totalManagementProject
        ->leftJoin('employee', function ($join) {
            $join->on('employee.id', '=', 'total_management_project.employee_id');
        })
        ->leftJoin('users', 'employee.id', '=', 'users.reference_id')
        ->leftJoin('transportation', function ($join) {
            $join->on('transportation.id', '=', 'total_management_project.driver_id')
            ->where('total_management_project.assign_as_supervisor', '=', 1);
        })
        ->where(function ($query) use ($request) {
            if(isset($request['search']) && !empty($request['search'])) {
                $query->where('employee.employee_name', 'like', '%'.$request['search'].'%')
                ->orWhere('transportation.driver_name', 'like', '%'.$request['search'].'%');
            }
        })
        ->select('total_management_project.employee_id','employee.employee_name', 'employee.contact_number', 'users.email', 'total_management_project.assign_as_supervisor', 'total_management_project.driver_id', 'transportation.driver_name', 'transportation.driver_contact_number', DB::raw('COUNT(total_management_project.id) as project_count'))
        ->groupBy('total_management_project.employee_id','employee.employee_name', 'employee.contact_number', 'users.email', 'total_management_project.assign_as_supervisor', 'total_management_project.driver_id', 'transportation.driver_name', 'transportation.driver_contact_number')
        ->paginate(Config::get('services.paginate_row'));
    }
    /**
     * @param $request
     * @return mixed
     */   
    public function viewAssignments($request): mixed
    {
        return $this->totalManagementProject
        ->leftJoin('employee', 'employee.id', '=', 'total_management_project.employee_id')
        ->leftJoin('vendors', 'vendors.id', '=', 'total_management_project.transportation_provider_id')
        ->leftJoin('transportation', 'transportation.id', '=', 'total_management_project.driver_id')
        ->where(function ($query) use ($request) {
            if(isset($request['employee_id']) && !empty($request['employee_id'])) {
                $query->where('total_management_project.employee_id', $request['employee_id']);
            }
            if(isset($request['driver_id']) && !empty($request['driver_id'])) {
                $query->where('total_management_project.driver_id', $request['driver_id']);
                $query->where('total_management_project.assign_as_supervisor', '=', 1);
            }
        })
        ->select('total_management_project.id', 'total_management_project.application_id', 'total_management_project.name', 'total_management_project.state', 'total_management_project.city', 'total_management_project.address', 'total_management_project.employee_id', 'employee.employee_name', 'total_management_project.transportation_provider_id', 'vendors.name', 'total_management_project.driver_id', 'transportation.driver_name', 'total_management_project.assign_as_supervisor', 'total_management_project.annual_leave', 'total_management_project.medical_leave', 'total_management_project.hospitalization_leave', 'total_management_project.created_at', 'total_management_project.updated_at')
        ->distinct('total_management_project.id')
        ->orderBy('total_management_project.id', 'desc')
        ->paginate(Config::get('services.paginate_row'));
    }
}