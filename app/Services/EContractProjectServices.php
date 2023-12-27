<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Config;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\EContractProject;
use App\Models\EContractProjectAttachments;
use App\Models\EContractApplications;

class EContractProjectServices
{
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
     * EContractProjectServices constructor.
     * @param EContractProject $eContractProject
     * @param EContractProjectAttachments $eContractProjectAttachments
     * @param Storage $storage
     * @param EContractApplications $eContractApplications
     */
    public function __construct(EContractProject $eContractProject, EContractProjectAttachments $eContractProjectAttachments, Storage $storage, EContractApplications $eContractApplications)
    {
        $this->eContractProject = $eContractProject;
        $this->eContractProjectAttachments = $eContractProjectAttachments;
        $this->storage = $storage;
        $this->eContractApplications = $eContractApplications;
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
            'annual_leave' => 'required|regex:/^[0-9]+$/|max:2',
            'medical_leave' => 'required|regex:/^[0-9]+$/|max:2',
            'hospitalization_leave' => 'required|regex:/^[0-9]+$/|max:2',
            'valid_until' => 'required|date|date_format:Y-m-d'
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
            'annual_leave' => 'required|regex:/^[0-9]+$/|max:2',
            'medical_leave' => 'required|regex:/^[0-9]+$/|max:2',
            'hospitalization_leave' => 'required|regex:/^[0-9]+$/|max:2',
            'valid_until' => 'required|date|date_format:Y-m-d'
        ];
    }
    /**
     * @param $request
     * @return mixed
     */   
    public function list($request): mixed
    {
        return $this->eContractProject
        ->leftJoin('worker_employment', function($query) {
            $query->on('worker_employment.project_id','=','e-contract_project.id')
            ->where('worker_employment.service_type', 'e-Contract')
            ->where('worker_employment.transfer_flag', 0)
            ->whereNull('worker_employment.remove_date');
        })
        ->leftJoin('workers', function($query) {
            $query->on('workers.id','=','worker_employment.worker_id')
            ->whereIN('workers.econtract_status', Config::get('services.ECONTRACT_WORKER_STATUS'));
        })
        ->join('e-contract_applications', function($query) use($request) {
            $query->on('e-contract_applications.id','=','e-contract_project.application_id')
            ->whereIn('e-contract_applications.company_id', $request['company_id']);
        })
        ->where('e-contract_project.application_id',$request['application_id'])
        ->where(function ($query) use ($request) {
            if(isset($request['search']) && !empty($request['search'])) {
                $query->where('e-contract_project.name', 'like', '%'.$request['search'].'%')
                ->orWhere('e-contract_project.state', 'like', '%'.$request['search'].'%')
                ->orWhere('e-contract_project.city', 'like', '%'.$request['search'].'%');
            }
        })
        ->select('e-contract_project.id', 'e-contract_project.application_id', 'e-contract_project.name', 'e-contract_project.state', 'e-contract_project.city', 'e-contract_project.address', 'e-contract_project.annual_leave', 'e-contract_project.medical_leave', 'e-contract_project.hospitalization_leave', 'e-contract_project.valid_until', 'e-contract_project.created_at', 'e-contract_project.updated_at')
        ->selectRaw('count(distinct workers.id) as workers, count(distinct worker_employment.id) as worker_employments')
        ->groupBy('e-contract_project.id', 'e-contract_project.application_id', 'e-contract_project.name', 'e-contract_project.state', 'e-contract_project.city', 'e-contract_project.address', 'e-contract_project.annual_leave', 'e-contract_project.medical_leave', 'e-contract_project.hospitalization_leave', 'e-contract_project.valid_until', 'e-contract_project.created_at', 'e-contract_project.updated_at')
        ->distinct('e-contract_project.id')
        ->orderBy('e-contract_project.id', 'desc')
        ->paginate(Config::get('services.paginate_row'));
    }
    /**
     * @param $request
     * @return mixed
     */   
    public function show($request): mixed
    {
        return $this->eContractProject->with('projectAttachments')
        ->join('e-contract_applications', function($query) use($request) {
            $query->on('e-contract_applications.id','=','e-contract_project.application_id')
            ->whereIn('e-contract_applications.company_id', $request['company_id']);
        })
        ->select('e-contract_project.*')
        ->find($request['id']);
    }
    /**
     * @param $request
     * @return bool|array
     */   
    public function add($request): bool|array
    {
        $user = JWTAuth::parseToken()->authenticate();
        $params = $request->all();
        $params['created_by'] = $user['id'];

        $validator = Validator::make($request->toArray(), $this->addValidation());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }
        $applicationDetails = $this->eContractApplications->whereIn('company_id', $request['company_id'])->find($request['application_id']);
        
        if(is_null($applicationDetails)){
            return [
                'unauthorizedError' => 'Unauthorized'
            ];
        }
        $eContractProject = $this->eContractProject->create([
            'application_id' => $request['application_id'] ?? 0,
            'name' => $request['name'] ?? '',
            'state' => $request['state'] ?? '',
            'city' => $request['city'] ?? '',
            'address' => $request['address'] ?? '',
            'annual_leave' => $request['annual_leave'] ?? 0,
            'medical_leave' => $request['medical_leave'] ?? 0,
            'hospitalization_leave' => $request['hospitalization_leave'] ?? 0,
            "valid_until" =>  $request['valid_until'] ?? null,
            'created_by' => $params['created_by'] ?? 0,
            'modified_by' => $params['created_by'] ?? 0
        ]);

        if (request()->hasFile('attachment') && isset($eContractProject['id']) && !empty($request['valid_until'])) {
            foreach($request->file('attachment') as $file) {                
                $fileName = $file->getClientOriginalName();                 
                $filePath = '/eContract/project/'. $fileName; 
                $linode = $this->storage::disk('linode');
                $linode->put($filePath, file_get_contents($file));
                $fileUrl = $this->storage::disk('linode')->url($filePath);

                $this->eContractProjectAttachments->updateOrCreate(
                    [
                        "file_id" => $eContractProject['id']
                    ],
                    [
                    "file_name" => $fileName,
                    "file_type" => 'Service Agreement',
                    "file_url" =>  $fileUrl,
                    'created_by' => $params['created_by'] ?? 0,
                    'modified_by' => $params['created_by'] ?? 0
                ]);
            }
        }

        return true;
    }
    /**
     * @param $request
     * @return bool|array
     */
    public function update($request): bool|array
    {
        $user = JWTAuth::parseToken()->authenticate();
        $params = $request->all();
        $params['modified_by'] = $user['id'];

        $validator = Validator::make($request->toArray(), $this->updateValidation());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }

        $eContractProject = $this->eContractProject
        ->join('e-contract_applications', function($query) use($request) {
            $query->on('e-contract_applications.id','=','e-contract_project.application_id')
            ->whereIn('e-contract_applications.company_id', $request['company_id']);
        })
        ->select('e-contract_project.*')
        ->find($request['id']);

        if(is_null($eContractProject)){
            return [
                'unauthorizedError' => 'Unauthorized'
            ];
        }
        
        $eContractProject->name =  $request['name'] ?? $eContractProject->name;
        $eContractProject->state =  $request['state'] ?? $eContractProject->state;
        $eContractProject->city =  $request['city'] ?? $eContractProject->city;
        $eContractProject->address =  $request['address'] ?? $eContractProject->address;
        $eContractProject->annual_leave =  $request['annual_leave'] ?? $eContractProject->annual_leave;
        $eContractProject->medical_leave =  $request['medical_leave'] ?? $eContractProject->medical_leave;
        $eContractProject->hospitalization_leave =  $request['hospitalization_leave'] ?? $eContractProject->hospitalization_leave;
        $eContractProject->valid_until =  $request['valid_until'] ?? $eContractProject->valid_until;
        $eContractProject->modified_by =  $params['modified_by'] ?? $eContractProject->modified_by;
        $eContractProject->save();

        if (request()->hasFile('attachment') && !empty($request['id']) && !empty($request['valid_until'])) {
            foreach($request->file('attachment') as $file) {                
                $fileName = $file->getClientOriginalName();                 
                $filePath = '/eContract/project/'. $fileName; 
                $linode = $this->storage::disk('linode');
                $linode->put($filePath, file_get_contents($file));
                $fileUrl = $this->storage::disk('linode')->url($filePath);

                $this->eContractProjectAttachments->updateOrCreate(
                    [
                        "file_id" => $request['id']
                    ],
                    [
                    "file_name" => $fileName,
                    "file_type" => 'Service Agreement',
                    "file_url" =>  $fileUrl,
                    'modified_by' => $params['modified_by'] ?? 0
                ]);
            }
        }
        return true;
    }
    /**
     * delete attachment
     * @param $request
     * @return array
     */    
    public function deleteAttachment($request): array
    {   
        //$data = $this->eContractProjectAttachments::find($request['attachment_id']); 

        $data = $this->eContractProjectAttachments
        ->join('e-contract_project', function($query) use($request) {
            $query->on('e-contract_project.id','=','e-contract_project_attachments.file_id');
        })
        ->join('e-contract_applications', function($query) use($request) {
            $query->on('e-contract_applications.id','=','e-contract_project.application_id')
            ->whereIn('e-contract_applications.company_id', $request['company_id']);
        })
        ->select('e-contract_project_attachments.*')
        ->find($request['attachment_id']);

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