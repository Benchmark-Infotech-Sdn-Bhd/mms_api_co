<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Config;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\DB;
use App\Models\CRMProspect;
use App\Models\CRMProspectService;
use App\Models\CRMProspectAttachment;
use App\Models\Services;
use App\Models\Sectors;
use App\Models\TotalManagementApplications;
use App\Models\DirectrecruitmentApplications;
use App\Models\Levy;
use App\Models\TotalManagementProject;

class totalManagementSupervisorServices
{
    /**
     * @var CRMProspect
     */
    private CRMProspect $crmProspect;
    /**
     * @var CRMProspectService
     */
    private CRMProspectService $crmProspectService;
    /**
     * @var CRMProspectAttachment
     */
    private CRMProspectAttachment $crmProspectAttachment;
    /**
     * @var Services
     */
    private Services $services;
    /**
     * @var Sectors
     */
    private Sectors $sectors;
    /**
     * @var TotalManagementApplications
     */
    private TotalManagementApplications $totalManagementApplications;
    /**
     * @var DirectrecruitmentApplications
     */
    private DirectrecruitmentApplications $directrecruitmentApplications;
    /**
     * @var Levy
     */
    private Levy $levy;
    /**
     * @var Storage
     */
    private Storage $storage;
    /**
     * @var TotalManagementProject
     */
    private TotalManagementProject $totalManagementProject;
    /**
     * TotalManagementServices constructor.
     * @param CRMProspect $crmProspect
     * @param CRMProspectService $crmProspectService
     * @param CRMProspectAttachment $crmProspectAttachment
     * @param Services $services
     * @param Sectors $sectors
     * @param TotalManagementApplications $totalManagementApplications
     * @param DirectrecruitmentApplications $directrecruitmentApplications
     * @param Levy $levy
     * @param Storage $storage
     * @param TotalManagementProject $totalManagementProject
     */
    public function __construct(CRMProspect $crmProspect, CRMProspectService $crmProspectService, 
    CRMProspectAttachment $crmProspectAttachment, Services $services, Sectors $sectors, TotalManagementApplications $totalManagementApplications, DirectrecruitmentApplications $directrecruitmentApplications, Levy $levy, Storage $storage, TotalManagementProject $totalManagementProject)
    {
        $this->crmProspect = $crmProspect;
        $this->crmProspectService = $crmProspectService;
        $this->crmProspectAttachment = $crmProspectAttachment;
        $this->services = $services;
        $this->sectors = $sectors;
        $this->totalManagementApplications = $totalManagementApplications;
        $this->directrecruitmentApplications = $directrecruitmentApplications;
        $this->levy = $levy;
        $this->storage = $storage;
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
            $join->on('employee.id', '=', 'total_management_project.employee_id')
            ->where('total_management_project.assign_as_supervisor', '=', 0);
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
            }
        })
        ->select('total_management_project.id', 'total_management_project.application_id', 'total_management_project.name', 'total_management_project.state', 'total_management_project.city', 'total_management_project.address', 'total_management_project.employee_id', 'employee.employee_name', 'total_management_project.transportation_provider_id', 'vendors.name', 'total_management_project.driver_id', 'transportation.driver_name', 'total_management_project.assign_as_supervisor', 'total_management_project.annual_leave', 'total_management_project.medical_leave', 'total_management_project.hospitalization_leave', 'total_management_project.created_at', 'total_management_project.updated_at')
        ->distinct('total_management_project.id')
        ->orderBy('total_management_project.id', 'desc')
        ->paginate(Config::get('services.paginate_row'));
    }
}