<?php

namespace App\Services;

use App\Models\Workers;
use App\Models\DirectRecruitmentCallingVisaStatus;
use App\Models\WorkerStatus;
use App\Models\DirectrecruitmentWorkers;
use App\Models\KinRelationship;
use App\Models\DirectRecruitmentOnboardingAgent;
use App\Models\WorkerBulkUpload;
use App\Models\DirectrecruitmentApplications;
use App\Models\DirectRecruitmentOnboardingCountry;
use App\Models\Levy;
use App\Services\DirectRecruitmentOnboardingCountryServices;
use App\Services\ValidationServices;
use Illuminate\Support\Facades\Config;
use App\Services\AuthServices;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use App\Services\WorkersServices;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\WorkerImport;

class DirectRecruitmentWorkersServices
{
    private Workers $workers;
    private DirectRecruitmentCallingVisaStatus $directRecruitmentCallingVisaStatus;
    private WorkerStatus $workerStatus;
    private KinRelationship $kinRelationship;
    private DirectRecruitmentOnboardingAgent $directRecruitmentOnboardingAgent;
    private WorkerBulkUpload $workerBulkUpload;
    private DirectRecruitmentOnboardingCountryServices $directRecruitmentOnboardingCountryServices;
    private ValidationServices $validationServices;
    private AuthServices $authServices;
    private Storage $storage;
    private DirectrecruitmentWorkers $directrecruitmentWorkers;
    private WorkersServices $workersServices;
    /**
     * @var DirectrecruitmentApplications
     */
    private DirectrecruitmentApplications $directrecruitmentApplications;
    /**
     * @var DirectRecruitmentOnboardingCountry
     */
    private DirectRecruitmentOnboardingCountry $directRecruitmentOnboardingCountry;
    /**
     * @var Levy
     */
    private Levy $levy;
    /**
     * DirectRecruitmentWorkersServices constructor.
     * @param Workers $workers
     * @param DirectRecruitmentCallingVisaStatus $directRecruitmentCallingVisaStatus
     * @param WorkerStatus $workerStatus
     * @param KinRelationship $kinRelationship
     * @param DirectRecruitmentOnboardingAgent $directRecruitmentOnboardingAgent
     * @param WorkerBulkUpload $workerBulkUpload
     * @param DirectRecruitmentOnboardingCountryServices $directRecruitmentOnboardingCountryServices;
     * @param ValidationServices $validationServices
     * @param AuthServices $authServices
     * @param Storage $storage
     * @param DirectrecruitmentWorkers $directrecruitmentWorkers;
     * @param WorkersServices $workersServices;
     * @param DirectrecruitmentApplications $directrecruitmentApplications;
     * @param DirectRecruitmentOnboardingCountry $directRecruitmentOnboardingCountry;
     * @param Levy $levy
     */
    public function __construct(
            Workers                                     $workers,
            DirectRecruitmentCallingVisaStatus          $directRecruitmentCallingVisaStatus,
            WorkerStatus                                $workerStatus,
            KinRelationship                             $kinRelationship,
            DirectRecruitmentOnboardingAgent            $directRecruitmentOnboardingAgent,
            WorkerBulkUpload                            $workerBulkUpload,
            DirectRecruitmentOnboardingCountryServices  $directRecruitmentOnboardingCountryServices, 
            ValidationServices                          $validationServices,
            AuthServices                                $authServices,
            Storage                                     $storage,
            DirectrecruitmentWorkers                    $directrecruitmentWorkers,
            WorkersServices                             $workersServices,
            DirectrecruitmentApplications               $directrecruitmentApplications,
            DirectRecruitmentOnboardingCountry          $directRecruitmentOnboardingCountry,
            Levy                                        $levy
    )
    {
        $this->workers = $workers;
        $this->workerStatus = $workerStatus;
        $this->kinRelationship = $kinRelationship;
        $this->directRecruitmentOnboardingAgent = $directRecruitmentOnboardingAgent;
        $this->workerBulkUpload = $workerBulkUpload;
        $this->validationServices = $validationServices;
        $this->directRecruitmentOnboardingCountryServices = $directRecruitmentOnboardingCountryServices;
        $this->authServices = $authServices;
        $this->storage = $storage;
        $this->directRecruitmentCallingVisaStatus = $directRecruitmentCallingVisaStatus;
        $this->directrecruitmentWorkers = $directrecruitmentWorkers;
        $this->workersServices = $workersServices;
        $this->directrecruitmentApplications = $directrecruitmentApplications;
        $this->directRecruitmentOnboardingCountry = $directRecruitmentOnboardingCountry;
        $this->levy = $levy;
    }
    /**
     * @return array
     */
    public function createWorkerValidation(): array
    {
        return
            [
                'onboarding_country_id' => 'required|regex:/^[0-9]+$/',
                'agent_id' => 'required|regex:/^[0-9]+$/',
                'application_id' => 'required|regex:/^[0-9]+$/'
            ];
    }
    /**
     * @return array
     */
    public function updateWorkerValidation(): array
    {
        return
            [
                'onboarding_country_id' => 'required|regex:/^[0-9]+$/',
                'agent_id' => 'required|regex:/^[0-9]+$/',
                'application_id' => 'required|regex:/^[0-9]+$/'
            ];
    }

    /**
     * @param $request
     * @return mixed
     */
    public function create($request) : mixed
    {
        $params = $request->all();
        $user = JWTAuth::parseToken()->authenticate();
        $params['created_by'] = $user['id'];

        $validator = Validator::make($request->toArray(), $this->createWorkerValidation());
        if($validator->fails()) {
            return [
                'validate' => $validator->errors()
            ];
        }

        $ksmReferenceNumbersResult = $this->directRecruitmentOnboardingCountryServices->ksmReferenceNumberList($params);

        $ksmReferenceNumbers = array();
        foreach ($ksmReferenceNumbersResult as $key => $ksmReferenceNumber) {
            $ksmReferenceNumbers[$key] = $ksmReferenceNumber['ksm_reference_number'];
        }
        
        if(isset($ksmReferenceNumbers) && !empty($ksmReferenceNumbers)){
            if(!in_array($request['ksm_reference_number'], $ksmReferenceNumbers)){
                return [
                    'ksmError' => true
                ];    
            }
        }

        $approvedCount = $this->levy->where('application_id', $request['application_id'])
                             ->where('new_ksm_reference_number', $request['ksm_reference_number'])
                             ->select('approved_quota')
                             ->first()->toArray();
        
        $onboardingCountryDetails = $this->directRecruitmentOnboardingCountry->findOrFail($request['onboarding_country_id']);

        $workerCount = $this->directrecruitmentWorkers
        ->leftjoin('worker_visa', 'directrecruitment_workers.worker_id', '=', 'worker_visa.worker_id')
        ->leftjoin('workers', 'workers.id', '=', 'worker_visa.worker_id')
        ->where([
            ['directrecruitment_workers.application_id', $request['application_id']],
            ['directrecruitment_workers.onboarding_country_id', $request['onboarding_country_id']],
            ['workers.cancel_status', 0]
        ])
        ->count('directrecruitment_workers.worker_id');

        if($workerCount >= $onboardingCountryDetails->quota) {
            return [
                'workerCountError' => true
            ]; 
        }

        $ksmReferenceNumberCount = $this->directrecruitmentWorkers
        ->leftjoin('worker_visa', 'directrecruitment_workers.worker_id', '=', 'worker_visa.worker_id')
        ->leftjoin('workers', 'workers.id', '=', 'worker_visa.worker_id')
        ->where([
            ['directrecruitment_workers.application_id', $request['application_id']],
            ['directrecruitment_workers.onboarding_country_id', $request['onboarding_country_id']],
            ['workers.cancel_status', 0],
            ['worker_visa.ksm_reference_number', $request['ksm_reference_number']],
        ])
        ->count('directrecruitment_workers.worker_id');

        if(isset($approvedCount) && ($ksmReferenceNumberCount >= $approvedCount['approved_quota'])) {
            return [
                'ksmCountError' => true
            ]; 
        }

        $applicationDetails = $this->directrecruitmentApplications->findOrFail($request['application_id']);
        $request['crm_prospect_id'] = $applicationDetails->crm_prospect_id;
        $data = $this->workersServices->create($request);

        if(isset($data['validate'])){
            return [
                'validate' => $data['validate']
            ];
        }else if(isset($data['id'])){

            $this->workers->where('id', $data['id'])
                ->update([
                    'module_type' => Config::get('services.WORKER_MODULE_TYPE')[0]
                ]);

            $directrecruitmentWorkers = $this->directrecruitmentWorkers::create([
                "worker_id" => $data['id'],
                'onboarding_country_id' => $request['onboarding_country_id'] ?? 0,
                'agent_id' => $request['agent_id'] ?? 0,
                'application_id' => $request['application_id'] ?? 0,
                'created_by'    => $params['created_by'] ?? 0,
                'modified_by'   => $params['created_by'] ?? 0   
            ]);

            $checkCallingVisa = $this->directRecruitmentCallingVisaStatus
            ->where('application_id', $request['application_id'])
            ->where('onboarding_country_id', $request['onboarding_country_id'])
            ->where('agent_id', $request['agent_id'])->get()->toArray();

            if(isset($checkCallingVisa) && count($checkCallingVisa) == 0 ){
                $callingVisaStatus = $this->directRecruitmentCallingVisaStatus->create([
                    'application_id' => $request['application_id'] ?? 0,
                    'onboarding_country_id' => $request['onboarding_country_id'] ?? 0,
                    'agent_id' => $request['agent_id'] ?? 0,
                    'item' => 'Calling Visa Status',
                    'updated_on' => Carbon::now(),
                    'status' => 1,
                    'created_by' => $params['created_by'] ?? 0,
                    'modified_by' => $params['created_by'] ?? 0,
                ]);
            }

            $checkWorkerStatus = $this->workerStatus
            ->where('application_id', $request['application_id'])
            ->where('onboarding_country_id', $request['onboarding_country_id'])
            ->get()->toArray();

            if(isset($checkWorkerStatus) && count($checkWorkerStatus) > 0 ){
                $this->workerStatus->where([
                    'application_id' => $request['application_id'],
                    'onboarding_country_id' => $request['onboarding_country_id']
                ])->update(['updated_on' => Carbon::now(), 'modified_by' => $params['created_by']]);
            } else {
                $workerStatus = $this->workerStatus->create([
                    'application_id' => $request['application_id'] ?? 0,
                    'onboarding_country_id' => $request['onboarding_country_id'] ?? 0,
                    'item' => 'Worker Biodata',
                    'updated_on' => Carbon::now(),
                    'status' => 1,
                    'created_by' => $params['created_by'] ?? 0,
                    'modified_by' => $params['created_by'] ?? 0,
                ]);
            }

            $onBoardingStatus['application_id'] = $request['application_id'];
            $onBoardingStatus['country_id'] = $request['onboarding_country_id'];
            $onBoardingStatus['onboarding_status'] = 4; //Agent Added
            $this->directRecruitmentOnboardingCountryServices->onboarding_status_update($onBoardingStatus);

            return true;

        }else{
            return false;
        }
    }

    /**
     * @param $request
     * @return mixed
     */
    public function list($request) : mixed
    {
        if(isset($request['search_param']) && !empty($request['search_param'])){
            if(!($this->validationServices->validate($request,['search_param' => 'required|min:3']))){
                return [
                    'validate' => $this->validationServices->errors()
                ];
            }
        }
        return $this->workers->join('worker_visa', 'workers.id', '=', 'worker_visa.worker_id')
        ->join('worker_bio_medical', 'workers.id', '=', 'worker_bio_medical.worker_id')
        ->leftjoin('worker_arrival', 'workers.id', '=', 'worker_arrival.worker_id')
        ->leftjoin('directrecruitment_workers', 'workers.id', '=', 'directrecruitment_workers.worker_id')
        ->where('directrecruitment_workers.application_id', $request['application_id'])
        ->where('directrecruitment_workers.onboarding_country_id', $request['onboarding_country_id'])
        ->where(function ($query) use ($request) {
            if (isset($request['stage_filter']) && $request['stage_filter'] == 'calling_visa') {
                $query->where('worker_visa.status','Processed');
            }

            if (isset($request['stage_filter']) && $request['stage_filter'] == 'arrival') {
                $query->where('worker_arrival.arrival_status','Not Arrived');
            }

            if (isset($request['stage_filter']) && $request['stage_filter'] == 'post_arrival') {
                $query->where('worker_arrival.arrival_status','Arrived');
            }

            if (isset($request['agent_id'])) {
                $query->where('directrecruitment_workers.agent_id',$request['agent_id']);
            }
            if (isset($request['status'])) {
                $query->where('worker_visa.approval_status',$request['status']);
            }
            
            if (isset($request['search_param']) && !empty($request['search_param'])) {
                $query->where('workers.name', 'like', "%{$request['search_param']}%")
                ->orWhere('workers.passport_number', 'like', '%'.$request['search_param'].'%')
                ->orWhere('worker_visa.ksm_reference_number', 'like', '%'.$request['search_param'].'%');
            }

        })->select('workers.id','workers.name','directrecruitment_workers.agent_id','workers.date_of_birth','workers.gender','workers.passport_number','workers.passport_valid_until','worker_visa.ksm_reference_number','worker_bio_medical.bio_medical_valid_until','worker_visa.approval_status as status', 'workers.cancel_status as cancellation_status', 'workers.created_at')
        ->distinct()
        ->orderBy('workers.created_at','DESC')
        ->paginate(Config::get('services.paginate_row'));
    }

    /**
     * @param $request
     * @return mixed
     */
    public function export($request) : mixed
    {
        if(isset($request['search_param']) && !empty($request['search_param'])){
            if(!($this->validationServices->validate($request,['search_param' => 'required|min:3']))){
                return [
                    'validate' => $this->validationServices->errors()
                ];
            }
        }
        return $this->workers->join('worker_visa', 'workers.id', '=', 'worker_visa.worker_id')
        ->join('worker_kin', 'workers.id', '=', 'worker_kin.worker_id')
        ->join('kin_relationship', 'kin_relationship.id', '=', 'worker_kin.kin_relationship_id')
        ->join('worker_bio_medical', 'workers.id', '=', 'worker_bio_medical.worker_id')
        ->leftjoin('worker_arrival', 'workers.id', '=', 'worker_arrival.worker_id')
        ->leftjoin('directrecruitment_workers', 'workers.id', '=', 'directrecruitment_workers.worker_id')
        ->where('directrecruitment_workers.application_id', $request['application_id'])
        ->where('directrecruitment_workers.onboarding_country_id', $request['onboarding_country_id'])
        ->where(function ($query) use ($request) {

            if (isset($request['stage_filter']) && $request['stage_filter'] == 'calling_visa') {
                $query->where('worker_visa.status','Processed');
            }

            if (isset($request['stage_filter']) && $request['stage_filter'] == 'arrival') {
                $query->where('worker_arrival.arrival_status','Not Arrived');
            }

            if (isset($request['stage_filter']) && $request['stage_filter'] == 'post_arrival') {
                $query->where('worker_arrival.arrival_status','Arrived');
            }
            
            if (isset($request['search_param']) && !empty($request['search_param'])) {
                $query->where('workers.name', 'like', "%{$request['search_param']}%")
                ->orWhere('workers.passport_number', 'like', '%'.$request['search_param'].'%')
                ->orWhere('worker_visa.ksm_reference_number', 'like', '%'.$request['search_param'].'%');
            }
            if (isset($request['status'])) {
                $query->where('workers.status',$request['status']);
            }
        })->select('workers.id','workers.name','workers.date_of_birth','workers.gender','workers.passport_number','workers.passport_valid_until','workers.address','workers.state','worker_kin.kin_name','kin_relationship.name as kin_relationship_name','worker_kin.kin_contact_number','worker_visa.ksm_reference_number','worker_bio_medical.bio_medical_reference_number','worker_bio_medical.bio_medical_valid_until')
        ->distinct()
        ->orderBy('workers.created_at','DESC')->get();
    }

    /**
     * @return mixed
     */
    public function dropdown($request) : mixed
    {
        return $this->workers->join('worker_visa', 'workers.id', '=', 'worker_visa.worker_id')
        ->leftjoin('directrecruitment_workers', 'workers.id', '=', 'directrecruitment_workers.worker_id')
        ->where('workers.status', 1)
        ->where('directrecruitment_workers.application_id', $request['application_id'])
        ->where('directrecruitment_workers.onboarding_country_id', $request['onboarding_country_id'])
        ->where('directrecruitment_workers.agent_id', $request['agent_id'])
        ->where('worker_visa.status', 'Pending')
        ->where(function ($query) use ($request) {
            if ($request['worker_id']) {
                $query->where('workers.id', '!=', $request['worker_id']);
            }
        })
        ->select('workers.id','workers.name')
        ->orderBy('workers.created_at','DESC')->get();
    }
    /**
     * @param $request
     * @return bool|array
     */
    public function update($request): bool|array
    {

        $params = $request->all();
        $user = JWTAuth::parseToken()->authenticate();
        $params['modified_by'] = $user['id'];

        $validator = Validator::make($request->toArray(), $this->updateWorkerValidation());
        if($validator->fails()) {
            return [
                'validate' => $validator->errors()
            ];
        }

        $ksmReferenceNumbersResult = $this->directRecruitmentOnboardingCountryServices->ksmReferenceNumberList($params);

        $ksmReferenceNumbers = array();
        foreach ($ksmReferenceNumbersResult as $key => $ksmReferenceNumber) {
            $ksmReferenceNumbers[$key] = $ksmReferenceNumber['ksm_reference_number'];
        }

        if(isset($ksmReferenceNumbers) && !empty($ksmReferenceNumbers)){
            if(!in_array($request['ksm_reference_number'], $ksmReferenceNumbers)){
                return [
                    'ksmError' => true
                ];    
            }
        }
        
        $data = $this->workersServices->update($request);

        if(isset($data['validate'])){
            return [
                'validate' => $data['validate']
            ];
        }

        $directrecruitmentWorkers = $this->directrecruitmentWorkers->where([
            ['application_id', $request['application_id']],
            ['worker_id', $request['id']]
        ])->first(['id', 'application_id', 'onboarding_country_id', 'agent_id', 'worker_id', 'created_by', 'modified_by', 'created_at', 'updated_at']);

        if(!empty($directrecruitmentWorkers)){
            $directrecruitmentWorkers->update([
                'onboarding_country_id' => $request['onboarding_country_id'] ?? $directrecruitmentWorkers->onboarding_country_id,
                'agent_id' => $request['agent_id'] ?? $directrecruitmentWorkers->agent_id,
                'modified_by' =>  $params['modified_by'] ?? 0,
                'updated_at' => Carbon::now()
            ]);
        }

        $this->workerStatus->where([
            'application_id' => $request['application_id'],
            'onboarding_country_id' => $request['onboarding_country_id']
        ])->update(['updated_on' => Carbon::now(), 'modified_by' => $params['modified_by']]);

        return true;
    }
    /**
     * @return mixed
     */
    public function kinRelationship() : mixed
    {
        return $this->kinRelationship->where('status', 1)
        ->select('id','name')
        ->orderBy('id','ASC')->get();
    }
    /**
     * @return mixed
     */
    public function onboardingAgent($request) : mixed
    {
        return $this->directRecruitmentOnboardingAgent
        ->join('agent', 'agent.id', '=', 'directrecruitment_onboarding_agent.agent_id')
        ->where('directrecruitment_onboarding_agent.status', 1)
        ->where('directrecruitment_onboarding_agent.application_id', $request['application_id'])
        ->where('directrecruitment_onboarding_agent.onboarding_country_id', $request['onboarding_country_id'])
        ->select('agent.id','agent.agent_name')
        ->orderBy('agent.id','ASC')->get();
    }
    /**
     * @param $request
     * @return array
     */
    public function replaceWorker($request) : array
    {
        $user = JWTAuth::parseToken()->authenticate();

        $worker = $this->workers
        ->where('id', $request['id'])
        ->update([
            'replace_worker_id' => $request['replace_worker_id'],
            'replace_by' => $user['id'],
            'replace_at' => Carbon::now()->format('Y-m-d H:i:s')
        ]);
        return  [
            "isUpdated" => $worker,
            "message" => "Updated Successfully"
        ];
    }
    /**
     * @param $request
     * @return mixed
     */
    public function show($request) : mixed
    {
        if(!($this->validationServices->validate($request,['id' => 'required']))){
            return [
                'validate' => $this->validationServices->errors()
            ];
        }
        return $this->workers->with('directrecruitmentWorkers', 'workerAttachments', 'workerKin', 'workerVisa', 'workerBioMedical', 'workerFomema', 'workerInsuranceDetails', 'workerBankDetails', 'workerFomemaAttachments')->findOrFail($request['id']);
    }
    /**
     * @param $request
     * @return mixed
     */
    public function import($request, $file): mixed
    {
        $params = $request->all();
        $user = JWTAuth::parseToken()->authenticate();
        $params['created_by'] = $user['id'];
        $params['modified_by'] = $user['id'];
        /* if(!($this->validationServices->validate($request->toArray(),$this->bulkUploadValidation()))){
            return [
              'validate' => $this->validationServices->errors()
            ];
        } */

        $workerBulkUpload = $this->workerBulkUpload->create([
                'onboarding_country_id' => $request['onboarding_country_id'] ?? '',
                'agent_id' => $request['agent_id'] ?? '',
                'application_id' => $request['application_id'] ?? '',
                'name' => 'Worker Bulk Upload',
                'type' => 'Worker bulk upload'
            ]
        );
        //echo "<pre>"; print_r($workerBulkUpload); exit;

        Excel::import(new WorkerImport($params, $workerBulkUpload), $file);
        return true;
    }
    /**
     * @param $request
     * @return mixed
     */
    public function workerStatusList($request): mixed
    {
        return $this->workerStatus
            ->select('id', 'item', 'updated_on', 'status')
            ->where([
                'application_id' => $request['application_id'],
                'onboarding_country_id' => $request['onboarding_country_id']
            ])
            ->orderBy('id', 'desc')
            ->paginate(Config::get('services.paginate_row'));
    }
    /**
     * @param $request
     * @return array
     */
    public function updateStatus($request) : array
    {
        $worker = $this->workers
        ->where('id', $request['id'])
        ->update(['status' => $request['status']]);
        return  [
            "isUpdated" => $worker,
            "message" => "Updated Successfully"
        ];
    }

}
