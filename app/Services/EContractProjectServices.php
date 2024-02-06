<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Config;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\EContractProject;
use App\Models\EContractProjectAttachments;
use App\Models\EContractApplications;
use App\Services\AuthServices;

class EContractProjectServices
{
    public const SERVICE_TYPE = 'e-Contract';
    public const SERVICE_AGREEMENT = 'Service Agreement';
    public const MESSAGE_DELETED_NOT_FOUND = "Data not found";
    public const MESSAGE_DELETED_SUCCESSFULLY = "Deleted Successfully";
    public const EMPLOYMENT_TRANSFER_FLAG = 0;
    public const ATTACHMENT_ACTION_CREATE = 'CREATE';
    public const ATTACHMENT_ACTION_UPDATE = 'UPDATE';
    public const DEFAULT_INTEGER_VALUE_ZERO = 0;
    public const UNAUTHORIZED_ERROR = 'Unauthorized';

    public const ERROR_UNAUTHORIZED = ['unauthorizedError' => self::UNAUTHORIZED_ERROR];

    /**
     * @var EContractProject
     */
    private EContractProject $eContractProject;

    /**
     * @var EContractProjectAttachments
     */
    private EContractProjectAttachments $eContractProjectAttachments;

    /**
     * @var Storage
     */
    private Storage $storage;

    /**
     * @var EContractApplications
     */
    private EContractApplications $eContractApplications;

    /**
     * @var AuthServices
     */
    private AuthServices $authServices;

    /**
     * Constructor method.
     * 
     * @param EContractProject $eContractProject Instance of the EContractProject class.
     * @param EContractProjectAttachments $eContractProjectAttachments Instance of the EContractProjectAttachments class.
     * @param Storage $storage Instance of the Storage class.
     * @param EContractApplications $eContractApplications Instance of the EContractApplications class.
     * @param AuthServices $authServices Instance of the AuthServices class.
     * 
     * @return void
     */
    public function __construct(
        EContractProject                $eContractProject,
        EContractProjectAttachments     $eContractProjectAttachments,
        Storage                         $storage,
        EContractApplications           $eContractApplications,
        AuthServices                    $authServices
    )
    {
        $this->eContractProject = $eContractProject;
        $this->eContractProjectAttachments = $eContractProjectAttachments;
        $this->storage = $storage;
        $this->eContractApplications = $eContractApplications;
        $this->authServices = $authServices;
    }

    /**
     * Creates the validation rules for creating a new e-contract project.
     *
     * @return array The array containing the validation rules.
     */
    public function addValidation(): array
    {
        return [
            'application_id' => 'required',
            'name' => 'required',
            'state' => 'required',
            'city' => 'required',
            'address' => 'required',
            'annual_leave' => 'required|regex:/^[0-9]+$/|max:2',
            'medical_leave' => 'required|regex:/^[0-9]+$/|max:2',
            'hospitalization_leave' => 'required|regex:/^[0-9]+$/|max:2',
            'valid_until' => 'required|date|date_format:Y-m-d'
        ];
    }

    /**
     * Creates the validation rules for updating the e-contract project.
     *
     * @return array The array containing the validation rules.
     */
    public function updateValidation(): array
    {
        return [
            'id' => 'required',
            'name' => 'required',
            'state' => 'required',
            'city' => 'required',
            'address' => 'required',
            'annual_leave' => 'required|regex:/^[0-9]+$/|max:2',
            'medical_leave' => 'required|regex:/^[0-9]+$/|max:2',
            'hospitalization_leave' => 'required|regex:/^[0-9]+$/|max:2',
            'valid_until' => 'required|date|date_format:Y-m-d'
        ];
    }

    /**
     * Returns a paginated list of e-contract project based on the given search request.
     * 
     * @param array $request The search request parameters.
     * @return mixed Returns a paginated list of e-contract project.
     */
    public function list($request): mixed
    {
        return $this->eContractProject
            ->leftJoin('worker_employment', function($query) {
                $this->applyWorkerEmploymentTableFilter($query);
            })
            ->leftJoin('workers', function($query) {
                $this->applyWorkerTableFilter($query);
            })
            ->join('e-contract_applications', function($query) use($request) {
                $this->applyEcontractApplicationFilter($query, $request);
            })
            ->where(function ($query) use ($request) {
                $this->applyEcontractProjectFilter($query, $request);
            })
            ->where(function ($query) use ($request) {
                $this->applySearchFilter($query, $request);
            })
            ->select('e-contract_project.id', 'e-contract_project.application_id', 'e-contract_project.name', 'e-contract_project.state', 'e-contract_project.city', 'e-contract_project.address', 'e-contract_project.annual_leave', 'e-contract_project.medical_leave', 'e-contract_project.hospitalization_leave', 'e-contract_project.valid_until', 'e-contract_project.created_at', 'e-contract_project.updated_at')
            ->selectRaw('count(distinct workers.id) as workers, count(distinct worker_employment.id) as worker_employments')
            ->groupBy('e-contract_project.id', 'e-contract_project.application_id', 'e-contract_project.name', 'e-contract_project.state', 'e-contract_project.city', 'e-contract_project.address', 'e-contract_project.annual_leave', 'e-contract_project.medical_leave', 'e-contract_project.hospitalization_leave', 'e-contract_project.valid_until', 'e-contract_project.created_at', 'e-contract_project.updated_at')
            ->distinct('e-contract_project.id')
            ->orderBy('e-contract_project.id', 'desc')
            ->paginate(Config::get('services.paginate_row'));
    }

    /**
     * Show the e-contract project with related project attachments.
     * 
     * @param array $request The request data containing e-contract project id,  company_id
     * @return mixed Returns the e-contract project details with related project attachments.
     */
    public function show($request): mixed
    {
        return $this->showEContractProject($request);
    }

    /**
     * Creates a new Project from the given request data.
     * 
     * @param $request The request data containing project details.
     * @return bool|array Returns an array with the following keys:
     * - "validate": An array of validation errors, if any.
     * - "unauthorizedError": A array returns unauthorized if e-contract applications is null.
     * - "isSubmit": A boolean indicating if the e-contract project was successfully created.
     */
    public function add($request): bool|array
    {
        $validationResult = $this->addValidateRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }

        $user = $this->getJwtUserAuthenticate();
        $request['created_by'] = $user['id'];
        
        $applicationDetails = $this->showEContractApplications($request);
        if (is_null($applicationDetails)) {
            return self::ERROR_UNAUTHORIZED;
        }

        $eContractProject = $this->createEContractProject($request);

        $this->updateEContractProjectAttachments(self::ATTACHMENT_ACTION_CREATE, $request, $eContractProject['id']);

        return true;
    }

    /**
     * Updates the project data with the given request.
     * 
     * @param $request The request data containing project details.
     * @return bool|array Returns an array with the following keys:
     * - "validate": An array of validation errors, if any.
     * - "unauthorizedError": A array returns unauthorized if e-contract project is null.
     * - "isSubmit": A boolean indicating if the e-contract project was successfully updated.
     */
    public function update($request): bool|array
    {
        $validationResult = $this->updateValidateRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }

        $user = $this->getJwtUserAuthenticate();
        $request['modified_by'] = $user['id'];
        $request['company_id'] = $this->getAuthUserCompanyIds($user);

        $eContractProject = $this->showEContractProject($request);
        if (is_null($eContractProject)) {
            return self::ERROR_UNAUTHORIZED;
        }

        $this->updateEContractProject($eContractProject, $request);

        $this->updateEContractProjectAttachments(self::ATTACHMENT_ACTION_UPDATE, $request, $request['id']);

        return true;
    }

    /**
     * Delete the e-contract project attachment
     * 
     * @param array $request The request data containing the attachment id and company id.
     * @return array The result of the delete operation containing the deletion status and message.
     */    
    public function deleteAttachment($request): array
    {
        $data = $this->eContractProjectAttachments
        ->join('e-contract_project', function($query) use($request) {
            $query->on('e-contract_project.id','=','e-contract_project_attachments.file_id');
        })
        ->join('e-contract_applications', function($query) use($request) {
            $this->applyEcontractApplicationFilter($query, $request);
        })
        ->select('e-contract_project_attachments.id', 'e-contract_project_attachments.file_id', 'e-contract_project_attachments.file_name', 'e-contract_project_attachments.file_type', 'e-contract_project_attachments.file_url', 'e-contract_project_attachments.created_by', 'e-contract_project_attachments.modified_by', 'e-contract_project_attachments.created_at', 'e-contract_project_attachments.updated_at', 'e-contract_project_attachments.deleted_at')
        ->find($request['attachment_id']);
        if (is_null($data)) {
            return [
                "isDeleted" => false,
                "message" => self::MESSAGE_DELETED_NOT_FOUND
            ];
        }

        return [
            "isDeleted" => $data->delete(),
            "message" => self::MESSAGE_DELETED_SUCCESSFULLY
        ];
    }

    /**
     * Creates a new Project from the given request data.
     *
     * @param array $request The array containing project data.
     *                      The array should have the following keys:
     *                      - application_id: The application of the project.
     *                      - name: The name of the project.
     *                      - state: The state of the project.
     *                      - city: The city of the project.
     *                      - address: The address of the project.
     *                      - annual_leave: The annual leave of the project.
     *                      - medical_leave: The medical leave of the project.
     *                      - hospitalization_leave: The hospitalization leave of the project.
     *                      - valid_until: The valid until of the project.
     *                      - created_by: The ID of the user who created the project.
     *
     * @return Project The newly created project object.
     */
    public function createEContractProject($request): mixed
    {
        return $this->eContractProject->create([
            'application_id' => $request['application_id'] ?? self::DEFAULT_INTEGER_VALUE_ZERO,
            'name' => $request['name'] ?? '',
            'state' => $request['state'] ?? '',
            'city' => $request['city'] ?? '',
            'address' => $request['address'] ?? '',
            'annual_leave' => $request['annual_leave'] ?? self::DEFAULT_INTEGER_VALUE_ZERO,
            'medical_leave' => $request['medical_leave'] ?? self::DEFAULT_INTEGER_VALUE_ZERO,
            'hospitalization_leave' => $request['hospitalization_leave'] ?? self::DEFAULT_INTEGER_VALUE_ZERO,
            "valid_until" =>  $request['valid_until'] ?? null,
            'created_by' => $request['created_by'] ?? self::DEFAULT_INTEGER_VALUE_ZERO,
            'modified_by' => $request['created_by'] ?? self::DEFAULT_INTEGER_VALUE_ZERO
        ]);
    }

    /**
     * Updates the project data with the given request.
     *
     * @param object $eContractProject The project object to be updated.
     * @param array $request The request containing the updated project data.
     *               - name: (string) The updated project name.
     *               - state: (int) The updated project state.
     *               - city: (int) The updated project city.
     *               - address: (string) The updated project address.
     *               - annual_leave: (int) The updated project annual leave.
     *               - medical_leave: (int) The updated project medical leave.
     *               - hospitalization_leave: (int) The updated project hospitalization leave.
     *               - valid_until: (int) The updated project valid until.
     *               - modified_by: (int) The updated project modified by.
     * 
     * @return void
     */
    public function updateEContractProject($eContractProject, $request): void
    {
        $eContractProject->name =  $request['name'] ?? $eContractProject->name;
        $eContractProject->state =  $request['state'] ?? $eContractProject->state;
        $eContractProject->city =  $request['city'] ?? $eContractProject->city;
        $eContractProject->address =  $request['address'] ?? $eContractProject->address;
        $eContractProject->annual_leave =  $request['annual_leave'] ?? $eContractProject->annual_leave;
        $eContractProject->medical_leave =  $request['medical_leave'] ?? $eContractProject->medical_leave;
        $eContractProject->hospitalization_leave =  $request['hospitalization_leave'] ?? $eContractProject->hospitalization_leave;
        $eContractProject->valid_until =  $request['valid_until'] ?? $eContractProject->valid_until;
        $eContractProject->modified_by =  $request['modified_by'] ?? $eContractProject->modified_by;
        $eContractProject->save();
    }

    /**
     * Upload attachment of e-contract project.
     *
     * @param string $action The action value find the [create or update] functionality
     * @param array $request The request data containing e-contract project attachments
     * @param int $eContractProjectId The attachments was upload against the application Id
     * 
     * @return void
     */
    public function updateEContractProjectAttachments($action, $request, $eContractProjectId): void
    {
        if (request()->hasFile('attachment') && isset($eContractProjectId) && !empty($request['valid_until'])) {
            foreach($request->file('attachment') as $file) {                
                $fileName = $file->getClientOriginalName();                 
                $filePath = '/eContract/project/'. $fileName; 
                $linode = $this->storage::disk('linode');
                $linode->put($filePath, file_get_contents($file));
                $fileUrl = $this->storage::disk('linode')->url($filePath);

                $processData = [
                    "file_name" => $fileName, "file_type" => self::SERVICE_AGREEMENT, "file_url" =>  $fileUrl
                ];

                if ($action == self::ATTACHMENT_ACTION_CREATE) {
                    $processData['created_by'] = $request['created_by'] ?? self::DEFAULT_INTEGER_VALUE_ZERO;
                    $processData['modified_by'] = $request['created_by'] ?? self::DEFAULT_INTEGER_VALUE_ZERO;
                } else {
                    $processData['modified_by'] = $request['modified_by'] ?? self::DEFAULT_INTEGER_VALUE_ZERO;
                }

                $this->eContractProjectAttachments->updateOrCreate(
                    ["file_id" => $eContractProjectId], $processData
                );
            }
        }
    }

    /**
     * Show the e-contract project with related attachment and application.
     * 
     * @param array $request The request data containing e-contract project id, company id
     * @return mixed Returns the e-contract project with related attachment and application.
     */
    public function showEContractProject($request): mixed
    {
        return $this->eContractProject->with('projectAttachments')
        ->join('e-contract_applications', function($query) use($request) {
            $this->applyEcontractApplicationFilter($query, $request);
        })
        ->select('e-contract_project.id', 'e-contract_project.application_id', 'e-contract_project.name', 'e-contract_project.state', 'e-contract_project.city', 'e-contract_project.address', 'e-contract_project.annual_leave', 'e-contract_project.medical_leave', 'e-contract_project.hospitalization_leave', 'e-contract_project.created_by', 'e-contract_project.modified_by', 'e-contract_project.valid_until', 'e-contract_project.created_at', 'e-contract_project.updated_at', 'e-contract_project.deleted_at')
        ->find($request['id']);
    }
    
    /**
     * Apply the "worker employment" filter to the query
     *
     * @param Illuminate\Database\Query\Builder $query The query builder instance
     *
     * @return void
     */
    private function applyWorkerEmploymentTableFilter($query)
    {
        $query->on('worker_employment.project_id','=','e-contract_project.id')
            ->where('worker_employment.service_type', self::SERVICE_TYPE)
            ->where('worker_employment.transfer_flag', self::EMPLOYMENT_TRANSFER_FLAG)
            ->whereNull('worker_employment.remove_date');
    }
    
    /**
     * Apply the "worker" filter to the query
     *
     * @param Illuminate\Database\Query\Builder $query The query builder instance
     *
     * @return void
     */
    private function applyWorkerTableFilter($query)
    {
        $query->on('workers.id','=','worker_employment.worker_id')
            ->whereIN('workers.econtract_status', Config::get('services.ECONTRACT_WORKER_STATUS'));
    }
    
    /**
     * Apply the "e-contract applications" filter to the query
     *
     * @param Illuminate\Database\Query\Builder $query The query builder instance
     * @param array $request The request data containing the company id
     *
     * @return void
     */
    private function applyEcontractApplicationFilter($query, $request)
    {
        $query->on('e-contract_applications.id','=','e-contract_project.application_id')
            ->whereIn('e-contract_applications.company_id', $request['company_id']);
    }
    
    /**
     * Apply the "e-contract project" filter to the query
     *
     * @param Illuminate\Database\Query\Builder $query The query builder instance
     * @param array $request The request data containing the application id
     *
     * @return void
     */
    private function applyEcontractProjectFilter($query, $request)
    {
        $query->where('e-contract_project.application_id', $request['application_id']);
    }
    
    /**
     * Apply search filter to the query.
     *
     * @param Illuminate\Database\Query\Builder $query The query builder instance
     * @param array $request The request data containing the search keyword.
     * 
     * @return void
     */
    private function applySearchFilter($query, $request)
    {
        if (!empty($request['search'])) {
            $query->where('e-contract_project.name', 'like', '%'.$request['search'].'%')
            ->orWhere('e-contract_project.state', 'like', '%'.$request['search'].'%')
            ->orWhere('e-contract_project.city', 'like', '%'.$request['search'].'%');
        }
    }

    /**
     * Validate the given request data.
     *
     * @param array $request The request data to be validated.
     * @return array|bool Returns an array with 'error' as key and validation error messages as value if validation fails. | Returns true if validation passes.
     */
    private function addValidateRequest($request): array|bool
    {
        $validator = Validator::make($request->toArray(), $this->addValidation());
        if ($validator->fails()) {
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
     * @return array|bool Returns an array with 'error' as key and validation error messages as value if validation fails. | Returns true if validation passes.
     */
    private function updateValidateRequest($request): array|bool
    {
        $validator = Validator::make($request->toArray(), $this->updateValidation());
        if ($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }

        return true;
    }
    
    /**
     * Show the e-contract application.
     * 
     * @param array $request The request data containing application id, company id
     * @return mixed Returns the e-contract application.
     */
    private function showEContractApplications($request)
    {
        return $this->eContractApplications->whereIn('company_id', $request['company_id'])->find($request['application_id']);
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

    /**
     * get the auth user of company ids.
     * @param array $user The user data containing the user details
     * 
     * @return mixed Returns the user company ids.
     */
    private function getAuthUserCompanyIds($user): mixed
    {
        return $this->authServices->getCompanyIds($user);
    }
}