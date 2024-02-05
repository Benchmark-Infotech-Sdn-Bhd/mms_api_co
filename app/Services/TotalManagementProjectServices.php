<?php

namespace App\Services;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Config;
use App\Models\TotalManagementProject;

class TotalManagementProjectServices
{
    public const DEFAULT_TRANSFER_FLAG = 0;
    public const DEFAULT_VALUE = 0;
    public const ERROR_UNAUTHORIZED = ['unauthorizedError' => true];

    /**
     * @var totalManagementProject
     */
    private TotalManagementProject $totalManagementProject;
    /**
     * TotalManagementProjectServices constructor.
     * 
     * @param TotalManagementProject $totalManagementProject The totalManagementProject object.
     */
    public function __construct(TotalManagementProject $totalManagementProject)
    {
        $this->totalManagementProject = $totalManagementProject;
    }
    /**
     * validate the add project request data
     * 
     * @return array The validation error messages if validation fails, otherwise false.
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
     * @return array The validation error messages if validation fails, otherwise false.
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
     * Validate the given request data.
     *
     * @param array $request The request data to be validated.
     * @return array|bool Returns an array with 'error' as key and validation error messages as value if validation fails.
     *                   Returns true if validation passes.
     */
    private function validateAddRequest($request): array|bool
    {
        $validator = Validator::make($request, $this->addValidation());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }

        return true;
    }

    /**
     * Validate the given request data.
     *
     * @param array $request The request data to be validated.
     * @return array|bool Returns an array with 'error' as key and validation error messages as value if validation fails.
     *                   Returns true if validation passes.
     */
    private function validateUpdateRequest($request): array|bool
    {
        $validator = Validator::make($request, $this->updateValidation());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }

        return true;
    }

    /**
     * list the total management projects
     * 
     * @param $request
     *        application_id (int) ID of the application
     *        search (string) search parameter
     * 
     * @return mixed Returns The paginated list of project
     * 
     * @see applyCondition()
     * @see applySearchFilter()
     * @see ListSelectColumns()
     * 
     */   
    public function list($request): mixed
    {
        $data = $this->totalManagementProject
            //->leftJoin('employee', 'employee.id', '=', 'total_management_project.employee_id')
            ->leftJoin('users', 'users.id', '=', 'total_management_project.supervisor_id')
            ->leftJoin('employee', 'employee.id', '=', 'users.reference_id')
            ->leftJoin('transportation as supervisorTransportation', 'supervisorTransportation.id', '=', 'users.reference_id')
            ->leftJoin('vendors', 'vendors.id', '=', 'total_management_project.transportation_provider_id')
            ->leftJoin('transportation', 'transportation.id', '=', 'total_management_project.driver_id')
            ->leftJoin('worker_employment', function($query) {
                $query->on('worker_employment.project_id','=','total_management_project.id')
                ->where('worker_employment.service_type', Config::get('services.WORKER_MODULE_TYPE')[1])
                ->where('worker_employment.transfer_flag', self::DEFAULT_TRANSFER_FLAG)
                ->whereNull('worker_employment.remove_date');
            })
            ->leftJoin('workers', function($query) {
                $query->on('workers.id','=','worker_employment.worker_id')
                ->whereIN('workers.total_management_status', Config::get('services.TOTAL_MANAGEMENT_WORKER_STATUS'));
            });
        $data = $this->applyCondition($request,$data);
        $data = $this->applySearchFilter($request,$data);
        $data = $this->ListSelectColumns($data)
                    ->orderBy('total_management_project.id', 'desc')
                    ->paginate(Config::get('services.paginate_row'));
        return $data;
    }

    /**
     * Apply condition to the query builder based on user data
     *
     * @param array $request The user data
     *        application_id (int) ID of the application
     *
     * @return $data Returns the query builder object with the applied condition
     */
    private function applyCondition($request,$data)
    {
        return $data->where('total_management_project.application_id',$request['application_id']);
    }

    /**
     * Apply search filter to the query builder based on user data
     *
     * @param array $request The user data
     *        search (string) search parameter
     *
     * @return $data Returns the query builder object with the applied search filter
     */
    private function applySearchFilter($request,$data)
    {
        return $data->where(function ($query) use ($request) {
            $search = $request['search'] ?? '';
            if(!empty($search)) {
                $query->where('total_management_project.name', 'like', '%'.$search.'%')
                ->orWhere('total_management_project.state', 'like', '%'.$search.'%')
                ->orWhere('total_management_project.city', 'like', '%'.$search.'%')
                ->orWhere('employee.employee_name', 'like', '%'.$search.'%');
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
        return $data->select('total_management_project.id', 'total_management_project.application_id', 'total_management_project.name', 'total_management_project.state', 'total_management_project.city', 'total_management_project.address', 'total_management_project.supervisor_id', 'total_management_project.supervisor_type', 'total_management_project.employee_id', 'employee.employee_name', 'total_management_project.transportation_provider_id', 'vendors.name as vendor_name', 'total_management_project.driver_id', 'transportation.driver_name', 'total_management_project.assign_as_supervisor', 'total_management_project.annual_leave', 'total_management_project.medical_leave', 'total_management_project.hospitalization_leave', 'total_management_project.created_at', 'total_management_project.updated_at')
        ->selectRaw('count(distinct workers.id) as workers, count(distinct worker_employment.id) as worker_employments, IF(total_management_project.supervisor_type = "employee", employee.employee_name, supervisorTransportation.driver_name) as supervisor_name')
        ->groupBy('total_management_project.id', 'total_management_project.application_id', 'total_management_project.name', 'total_management_project.state', 'total_management_project.city', 'total_management_project.address', 'total_management_project.supervisor_id', 'total_management_project.supervisor_type', 'total_management_project.employee_id', 'employee.employee_name', 'total_management_project.transportation_provider_id', 'vendors.name', 'total_management_project.driver_id', 'transportation.driver_name', 'total_management_project.assign_as_supervisor', 'total_management_project.annual_leave', 'total_management_project.medical_leave', 'total_management_project.hospitalization_leave', 'total_management_project.created_at', 'total_management_project.updated_at', 'supervisorTransportation.driver_name');
    }

    /**
     * show the total management project detail
     * 
     * @param $request
     *        id (int) The ID of the project
     *        company_id (array) ID of the user company
     * 
     * @return mixed Returns the total management project record
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
     * 
     * @return bool|array Returns true if the create is successful. Returns an error array if validation fails or any error occurs during the create process.
     * 
     * @see validateAddRequest()
     * @see createTotalMangementProject()
     * 
     */   
    public function add($request): bool|array
    {
        $validationResult = $this->validateAddRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }
        $this->createTotalMangementProject($request);
        return true;
    }
    /**
     * create a new entry for total management project.
     *
     * @param array $request
     *              application_id (int) ID of the application
     *              name (string) name of the project
     *              state (string) project state
     *              city  (string) project city
     *              address (string) project address
     *              supervisor_id (int) ID of the supervisor 
     *              supervisor_type (string) supervisor type
     *              transportation_provider_id (int) ID of transportation provider
     *              driver_id (int) ID of the driver
     *              annual_leave (int) annual leave
     *              medical_leave (int) medical leave
     *              hospitalization_leave (int) hospitalization leave
     *              created_by (int) The ID of the user who created the project.
     * 
     * @return void
     */
    private function createTotalMangementProject($request)
    {
        $this->totalManagementProject->create([
            'application_id' => $request['application_id'] ?? self::DEFAULT_VALUE,
            'name' => $request['name'] ?? '',
            'state' => $request['state'] ?? '',
            'city' => $request['city'] ?? '',
            'address' => $request['address'] ?? '',
            'supervisor_id' => $request['supervisor_id'] ?? self::DEFAULT_VALUE,
            'supervisor_type' => $request['supervisor_type'] ?? self::DEFAULT_VALUE,
            //'employee_id' => $request['employee_id'] ?? 0,
            'transportation_provider_id' => $request['transportation_provider_id'] ?? self::DEFAULT_VALUE,
            'driver_id' => $request['driver_id'] ?? self::DEFAULT_VALUE,
            //'assign_as_supervisor' => $request['assign_as_supervisor'] ?? 0,
            'annual_leave' => $request['annual_leave'] ?? self::DEFAULT_VALUE,
            'medical_leave' => $request['medical_leave'] ?? self::DEFAULT_VALUE,
            'hospitalization_leave' => $request['hospitalization_leave'] ?? self::DEFAULT_VALUE,
            'created_by' => $request['created_by'] ?? self::DEFAULT_VALUE,
            'modified_by' => $request['created_by'] ?? self::DEFAULT_VALUE
        ]);
        
    }
    /**
     * update the total management project
     * 
     * @param $request
     *        id (int) ID of the project
     *        request data containg the total mangement project update data
     * 
     * @return bool|array Returns true if the update is successful. Returns an error array if validation fails or any error occurs during the update process.
     * 
     * @see validateUpdateRequest()
     * @see getTotalManagementProject()
     * @see updateTotalManagementProject()
     * 
     */
    public function update($request): bool|array
    {
        $validationResult = $this->validateUpdateRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }
        $totalManagementProject = $this->getTotalManagementProject($request);
        if(is_null($totalManagementProject)){
            return self::ERROR_UNAUTHORIZED;
        }
        $this->updateTotalManagementProject($totalManagementProject, $request);
        return true;
    }
    /**
     * Retrieve totalmanagement project record.
     *
     * @param array $request
     *              id (int) ID of the project
     *              company_id (int) ID of the user company
     * 
     * @return mixed Returns the project record
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
     *        name (string) name of the project
     *        state (string) project state
     *        city  (string) project city
     *        address (string) project address
     *        supervisor_id (int) ID of the supervisor 
     *        supervisor_type (string) supervisor type
     *        transportation_provider_id (int) ID of transportation provider
     *        driver_id (int) ID of the driver
     *        annual_leave (int) annual leave
     *        medical_leave (int) medical leave
     *        hospitalization_leave (int) hospitalization leave
     *        modified_by (int) The ID of the user who modified the project.
     * 
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