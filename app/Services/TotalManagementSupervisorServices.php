<?php

namespace App\Services;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use App\Models\TotalManagementProject;

class TotalManagementSupervisorServices
{
    public const DEFAULT_VALUE = 0;

    /**
     * @var totalManagementProject
     */
    private TotalManagementProject $totalManagementProject;
    /**
     * TotalManagementSupervisorServices constructor.
     * 
     * @param TotalManagementProject $totalManagementProject Instance of the TotalManagementProject class
     * 
     * @return void
     * 
     */
    public function __construct(TotalManagementProject $totalManagementProject)
    {
        $this->totalManagementProject = $totalManagementProject;
    }
    /**
     * list the total management project supervisor
     * 
     * @param $request
     *        company_id (array) ID of the user company
     *        search (string) search parameter
     * 
     * @return mixed Returns The paginated list of supervisor
     * 
     * @see listApplyCondition()
     * @see listApplySearchFilter()
     * @see listSelectColumns()
     * 
     */   
    public function list($request): mixed
    {
        $data = $this->totalManagementProject
        ->leftJoin('users', 'users.id', '=', 'total_management_project.supervisor_id')
        ->leftJoin('employee', 'employee.id', '=', 'users.reference_id')
        ->leftJoin('transportation as supervisorTransportation', 'supervisorTransportation.id', '=', 'users.reference_id');
        $data = $this->listApplyCondition($request,$data);  
        $data = $this->listApplySearchFilter($request,$data);
        $data = $this->listSelectColumns($data)
                        ->orderBy('total_management_project.id', 'desc')
                        ->paginate(Config::get('services.paginate_row'));
        return $data;
    }

     /**
     * Apply condition to the query builder based on user data
     *
     * @param array $request The user data
     *        company_id (array) ID of the user company
     *
     * @return $data Returns the query builder object with the applied condition
     */
    private function listApplyCondition($request,$data)
    {
        return $data->whereIn('employee.company_id', $request['company_id'])
        ->where('total_management_project.supervisor_id', '!=', self::DEFAULT_VALUE);
    }

    /**
     * Apply search filter to the query builder based on user data
     *
     * @param array $request The user data
     *        search (string) search parameter
     *
     * @return $data Returns the query builder object with the applied search filter
     */
    private function listApplySearchFilter($request,$data)
    {
        return $data->where(function ($query) use ($request) {
            $search = $request['search'] ?? '';
              if(!empty($search)) {
                $query->where('employee.employee_name', 'like', '%'.$search.'%')
                ->orWhere('supervisorTransportation.driver_name', 'like', '%'.$search.'%');
            }
        });
    }

    /**
     * Select data from the query.
     *
     * @return $data The modified instance of the class.
     */
    private function listSelectColumns($data)
    {
        return $data->select('total_management_project.id','total_management_project.supervisor_id', 'total_management_project.supervisor_type')
        ->selectRaw("(CASE WHEN (total_management_project.supervisor_type = 'employee') THEN employee.employee_name WHEN (total_management_project.supervisor_type = 'driver') THEN supervisorTransportation.driver_name ELSE null END) as supervisor_name, (CASE WHEN (total_management_project.supervisor_type = 'employee') THEN users.email WHEN (total_management_project.supervisor_type = 'driver') THEN supervisorTransportation.driver_email ELSE null END) as email, (CASE WHEN (total_management_project.supervisor_type = 'employee') THEN employee.contact_number WHEN (total_management_project.supervisor_type = 'driver') THEN supervisorTransportation.driver_contact_number ELSE null END) as contact_number")
        ->distinct('total_management_project.supervisor_id')
        ->groupBy('total_management_project.id','total_management_project.supervisor_id', 'total_management_project.supervisor_type','employee.employee_name', 'supervisorTransportation.driver_name', 'users.email', 'employee.contact_number', 'supervisorTransportation.driver_email', 'supervisorTransportation.driver_contact_number');
    }

    /**
     * list the total management project assignments
     * 
     * @param $request
     *        company_id (int) ID of the user company
     *        employee_id (int) ID of the employee
     *        driver_id (int) ID of the driver
     *        supervisor_id (int) ID of the supervisor
     *        search (string) search parameter
     * 
     * @return mixed Returns The paginated list of assignments
     * 
     * @see assignmentsApplyCondition()
     * @see assignmentsSelectColumns()
     * 
     */   
    public function viewAssignments($request): mixed
    {
        $data = $this->totalManagementProject
        ->leftJoin('total_management_applications', 'total_management_applications.id', '=', 'total_management_project.application_id')
        ->leftJoin('crm_prospects', 'crm_prospects.id', '=', 'total_management_applications.crm_prospect_id')
        ->leftJoin('users as supervisorUsers', 'supervisorUsers.id', '=', 'total_management_project.supervisor_id')
        ->leftJoin('employee', 'employee.id', '=', 'supervisorUsers.reference_id')
        ->leftJoin('transportation as supervisorTransportation', 'supervisorTransportation.id', '=', 'supervisorUsers.reference_id')
        ->leftJoin('vendors', 'vendors.id', '=', 'total_management_project.transportation_provider_id')
        ->leftJoin('transportation', 'transportation.id', '=', 'total_management_project.driver_id');
        $data = $this->assignmentsApplyCondition($request,$data);
        $data = $this->assignmentsSelectColumns($data)
                    ->orderBy('total_management_project.id', 'desc')
                    ->paginate(Config::get('services.paginate_row'));
        return $data;
    }
    /**
     * Apply condition to the query builder based on user data
     *
     * @param array $request The user data
     *              company_id (array) ID of the user company
     *              employee_id (int) ID of the employer
     *              driver_id (int) ID of the driver
     *              supervisor_id (int) ID of the supervisor
     *              search (string) search param
     *        
     *
     * @return $data Returns the query builder object with the applied condition
     */
    private function assignmentsApplyCondition($request,$data)
    {
        return $data->where(function ($query) use ($request) {
            $employeeId = $request['employee_id'] ?? '';
            $driverId = $request['driver_id'] ?? '';
            $supervisorId = $request['supervisor_id'] ?? '';
            $search = $request['search'] ?? '';
            if(!empty($employeeId)) {  
                $query->where('total_management_project.supervisor_id', $employeeId);
            }
            if(!empty($driverId)) {  
                $query->where('total_management_project.driver_id', $driverId);
                //$query->where('total_management_project.assign_as_supervisor', '=', 1);
             }
             if(!empty($supervisorId)) {   
                $query->where('total_management_project.supervisor_id', $supervisorId);
            }
            if(!empty($search)) {
                $query->where('crm_prospects.company_name', 'like', '%'.$search.'%')
                ->orWhere('total_management_project.name', 'like', '%'.$search.'%');
            }
        })
        ->whereIn('employee.company_id', $request['company_id']);
    }

    /**
     * Select data from the query.
     *
     * @return $data The modified instance of the class.
     */
    private function assignmentsSelectColumns($data)
    {
        return $data->select('crm_prospects.company_name', 'total_management_project.id', 'total_management_project.application_id', 'total_management_project.name', 'total_management_project.employee_id', 'employee.employee_name', 'employee.position', 'total_management_project.transportation_provider_id', 'vendors.name as vendor_name', 'total_management_project.driver_id', 'transportation.driver_name', 'total_management_project.assign_as_supervisor', 'total_management_project.created_at','total_management_project.supervisor_id', 'total_management_project.supervisor_type')
        ->selectRaw("(CASE WHEN (total_management_project.supervisor_type = 'employee') THEN employee.employee_name WHEN (total_management_project.supervisor_type = 'driver') THEN supervisorTransportation.driver_name ELSE null END) as supervisor_name")
        ->distinct('total_management_project.id');
    }
}