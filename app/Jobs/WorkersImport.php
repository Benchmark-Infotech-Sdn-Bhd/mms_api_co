<?php

namespace App\Jobs;

use App\Models\DirectrecruitmentWorkers;
use App\Models\Workers;
use App\Models\WorkerKin;
use App\Models\WorkerVisa;
use App\Models\WorkerBioMedical;
use App\Models\BulkUploadRecords;
use App\Models\WorkerFomema;
use App\Models\WorkerInsuranceDetails;
use App\Models\WorkerBankDetails;
use App\Models\DirectrecruitmentApplications;
use App\Models\DirectRecruitmentCallingVisaStatus;
use App\Models\DirectRecruitmentOnboardingCountry;
use App\Models\DirectRecruitmentOnboardingAgent;
use App\Models\WorkerStatus;
use App\Models\Levy;
use App\Services\ManageWorkersServices;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;


class WorkersImport extends Job
{
    private $parameters;
    private $bulkUpload;
    private $workerParameter;
    Private $workerNonMandatory;

    /**
     * Create a new job instance.
     *
     * @param $workerParameter
     * @param $parameters
     * @param $bulkUpload
     * @param $workerNonMandatory
     */
    public function __construct($workerParameter, $bulkUpload, $workerNonMandatory)
    {
        $this->workerParameter = $workerParameter;
        $this->bulkUpload = $bulkUpload;
        $this->workerNonMandatory = $workerNonMandatory;
    }

    /**
     * @return array
     */
    public function formatValidation(): array
    {
        return [
            'onboarding_country_id' => 'required',
            'agent_id' => 'required',
            'application_id' => 'required',
            'name' => 'required|regex:/^[a-zA-Z ]*$/|max:255',
            'date_of_birth' => 'required|date|date_format:Y-m-d',
            'gender' => 'required|regex:/^[a-zA-Z]*$/|max:15',                        
            'passport_number' => 'required|regex:/^[a-zA-Z0-9]*$/|unique:workers',
            'passport_valid_until' => 'required|date|date_format:Y-m-d',
            'address' => 'required',
            'state' => 'required|regex:/^[a-zA-Z ]*$/|max:150',
            'kin_name' => 'required|regex:/^[a-zA-Z]*$/|max:255',
            'kin_relationship' => 'required',
            'kin_contact_number' => 'required|regex:/^[0-9]+$/',
            'ksm_reference_number' => 'required',
            'bio_medical_reference_number' => 'required|max:255',
            'bio_medical_valid_until' => 'required|date|date_format:Y-m-d'
        ];
    }

    /**
     * Execute the job.
     *
     * @param ManageWorkersServices $manageWorkersServices
     * @return void
     * @throws \JsonException
     */
    public function handle(ManageWorkersServices $manageWorkersServices): void
    { 

        Log::info('Worker insert - started ');

        $comments = '';
        $countryQuotaError = 0;
        $ksmReferenceNumberQuotaError = 0;
        $agentWorkerCountError = 0;
        $successFlag = 0;
        //$validationCheck = $this->createValidation($this->workerParameter);

        $validationError = [];
        $validator = Validator::make($this->workerParameter, $this->formatValidation());
        if($validator->fails()) {
            
            $validationError = str_replace(".","", implode(",",$validator->messages()->all()));
            
            //Log::info('validationError' . print_r($validator->errors(), true));

            //Log::info('error' . print_r($validationError, true));

        }

        if(empty($validationError)) {

            $workerRelationship = DB::table('kin_relationship')->where('name', $this->workerParameter['kin_relationship'])->first('id');

            Log::info('Row Data - realtionship - ' . print_r($workerRelationship->id, true));

            $workerCount = DB::table('workers')->where('passport_number', $this->workerParameter['passport_number'])->count();

            if($workerCount > 0){
                Log::info('worker - passport already exist '.$this->workerParameter['passport_number']);
                $comments .= 'ERROR - worker - passport already exist '.$this->workerParameter['passport_number'];
            }
            
            $applicationId = $this->workerParameter['application_id'];

            $ksmReferenceNumbersResult = DB::table('directrecruitment_application_approval')
            ->leftJoin('levy', function($join) use ($applicationId){
                $join->on('levy.application_id', '=', 'directrecruitment_application_approval.application_id')
                ->on('levy.new_ksm_reference_number', '=', 'directrecruitment_application_approval.ksm_reference_number');
                })
            ->leftJoin('directrecruitment_onboarding_countries', 'directrecruitment_onboarding_countries.application_id', 'directrecruitment_application_approval.application_id')
            ->where('directrecruitment_application_approval.application_id', $applicationId)
            ->select('directrecruitment_application_approval.application_id', 'directrecruitment_application_approval.ksm_reference_number', 'levy.approved_quota')
            ->selectRaw('sum(directrecruitment_onboarding_countries.utilised_quota) as utilised_quota')
            ->groupBy('directrecruitment_application_approval.application_id', 'directrecruitment_application_approval.ksm_reference_number', 'levy.approved_quota')
            ->distinct()
            ->get();

            

            $ksmReferenceNumbers = array();
            foreach ($ksmReferenceNumbersResult as $key => $ksmReferenceNumber) {
                Log::info('ksm result - ' . ($ksmReferenceNumber->ksm_reference_number));
                $ksmReferenceNumbers[$key] = $ksmReferenceNumber->ksm_reference_number;
            }
            
            $ksmError = 0; 
            if(isset($ksmReferenceNumbers) && !empty($ksmReferenceNumbers)){
                if(!in_array($this->workerParameter['ksm_reference_number'], $ksmReferenceNumbers)){
                    Log::info('Row Data - KSM Reference Number Error - ' . print_r($workerRelationship->id, true));   
                    $ksmError = 1; 
                    $comments .= 'ERROR - KSM Reference Number is mis-matched.';
                }
            }

        $onboardingCountryDetails = DirectRecruitmentOnboardingCountry::findOrFail($this->workerParameter['onboarding_country_id']);

        $assignedWorkers = DB::table('directrecruitment_workers')
        ->leftjoin('worker_visa', 'directrecruitment_workers.worker_id', '=', 'worker_visa.worker_id')
        ->leftjoin('workers', 'workers.id', '=', 'worker_visa.worker_id')
        ->where([
            ['directrecruitment_workers.application_id', $this->workerParameter['application_id']],
            ['directrecruitment_workers.onboarding_country_id', $this->workerParameter['onboarding_country_id']],
            ['workers.cancel_status', 0]
        ])
        ->whereIn('workers.directrecruitment_status', Config::get('services.DIRECT_RECRUITMENT_WORKER_STATUS'))
        ->count('directrecruitment_workers.worker_id');

        Log::info('workers count - ' . $assignedWorkers);
        Log::info('country quota - ' . $onboardingCountryDetails->quota);  

        if($assignedWorkers >= $onboardingCountryDetails->quota) {
            $countryQuotaError = 1;
            $comments .= 'ERROR - country quota cannot exceeded';
        }

        $approvedCount = Levy::where('application_id', $this->workerParameter['application_id'])
                             ->where('new_ksm_reference_number', $this->workerParameter['ksm_reference_number'])
                             ->first('approved_quota');

        if(isset($approvedCount['approved_quota'])){
            Log::info('levy approved count - ' . $approvedCount['approved_quota']);
        }
        
        $ksmReferenceNumberCount = DB::table('directrecruitment_workers')
        ->leftjoin('worker_visa', 'directrecruitment_workers.worker_id', '=', 'worker_visa.worker_id')
        ->leftjoin('workers', 'workers.id', '=', 'worker_visa.worker_id')
        ->where([
            ['directrecruitment_workers.application_id', $this->workerParameter['application_id']],
            ['workers.cancel_status', 0],
            ['worker_visa.ksm_reference_number', $this->workerParameter['ksm_reference_number']],
        ])
        ->whereIn('workers.directrecruitment_status', Config::get('services.DIRECT_RECRUITMENT_WORKER_STATUS'))
        ->count('directrecruitment_workers.worker_id');

        Log::info('ksm reference number count - ' . $ksmReferenceNumberCount);

        if(isset($approvedCount['approved_quota']) && ($ksmReferenceNumberCount >= $approvedCount['approved_quota'])) {
            $ksmReferenceNumberQuotaError = 1;
            $comments .= ' ERROR - ksm reference number quota cannot exceeded';
        }

        $agentDetails = DirectRecruitmentOnboardingAgent::findOrFail($this->workerParameter['agent_id']);

        $agentWorkerCount = DB::table('directrecruitment_workers')
        ->leftjoin('worker_visa', 'directrecruitment_workers.worker_id', '=', 'worker_visa.worker_id')
        ->leftjoin('workers', 'workers.id', '=', 'worker_visa.worker_id')
        ->where([
            ['directrecruitment_workers.application_id', $this->workerParameter['application_id']],
            ['directrecruitment_workers.onboarding_country_id', $this->workerParameter['onboarding_country_id']],
            ['directrecruitment_workers.agent_id', $this->workerParameter['agent_id']],
            ['workers.cancel_status', 0]
        ])
        ->whereIn('workers.directrecruitment_status', Config::get('services.DIRECT_RECRUITMENT_WORKER_STATUS'))
        ->count('directrecruitment_workers.worker_id');

        if($agentWorkerCount >= $agentDetails->quota) {
            $agentWorkerCountError = 1;
            $comments .= ' ERROR - agent worker quota count cannot exceeded'; 
        }

            if($workerCount == 0 && $ksmError == 0 && $countryQuotaError == 0 && $ksmReferenceNumberQuotaError == 0 && $agentWorkerCountError == 0){

                $applicationDetails = DirectrecruitmentApplications::findOrFail($this->workerParameter['application_id']);
                $prospect_id = $applicationDetails->crm_prospect_id;

                $worker = Workers::create([            
                    'name' => $this->workerParameter['name'] ?? '',
                    'gender' => $this->workerParameter['gender'] ?? '',
                    'date_of_birth' => $this->workerParameter['date_of_birth'] ?? '',
                    'passport_number' => $this->workerParameter['passport_number'] ?? '',
                    'passport_valid_until' => $this->workerParameter['passport_valid_until'] ?? '',
                    'fomema_valid_until' => null,
                    'status' => 1,
                    'address' => $this->workerParameter['address'] ?? '',
                    'city' => $this->workerNonMandatory['city'] ?? '',
                    'state' => $this->workerParameter['state'] ?? '',
                    'crm_prospect_id' => $prospect_id ?? NULL,
                    'created_by'    => $this->workerParameter['created_by'] ?? 0,
                    'modified_by'   => $this->workerParameter['created_by'] ?? 0,
                    'company_id'    => $applicationDetails->company_id,
                    'created_at'    => Carbon::now()->toDateTimeString(),
                    'updated_at'    => Carbon::now()->toDateTimeString()
                ]);

                Workers::where('id', $worker['id'])
                ->update([
                    'module_type' => 'Direct Recruitment'
                ]);
    
                DirectrecruitmentWorkers::create([
                    "worker_id" => $worker['id'],
                    'onboarding_country_id' => $this->workerParameter['onboarding_country_id'] ?? 0,
                    'agent_id' => $this->workerParameter['agent_id'] ?? 0,
                    'application_id' => $this->workerParameter['application_id'] ?? 0,
                    'created_by'    => $this->workerParameter['created_by'] ?? 0,
                    'modified_by'   => $this->workerParameter['created_by'] ?? 0 ,
                    'created_at'    => Carbon::now()->toDateTimeString(),
                    'updated_at'    => Carbon::now()->toDateTimeString()         
                ]);
    
                WorkerKin::create([
                    "worker_id" => $worker['id'],
                    "kin_name" => $this->workerParameter['kin_name'] ?? '',
                    "kin_relationship_id" => $workerRelationship->id ?? 0,
                    "kin_contact_number" =>  $this->workerParameter['kin_contact_number'] ?? '',
                    'created_at'    => Carbon::now()->toDateTimeString(),
                    'updated_at'    => Carbon::now()->toDateTimeString()         
                ]);
    
                WorkerVisa::create([
                    "worker_id" => $worker['id'],
                    "ksm_reference_number" => $this->workerParameter['ksm_reference_number'],
                    "calling_visa_reference_number" =>  null, 
                    "calling_visa_valid_until" =>  null,         
                    "entry_visa_valid_until" =>  null,
                    "work_permit_valid_until" =>  null
                ]);
    
                WorkerBioMedical::create([
                    "worker_id" => $worker['id'],
                    "bio_medical_reference_number" => $this->workerParameter['bio_medical_reference_number'],
                    "bio_medical_valid_until" => $this->workerParameter['bio_medical_valid_until'],
                ]);

                WorkerFomema::create([
                    "worker_id" => $worker['id'],
                    "purchase_date" => ((isset($request['purchase_date']) && !empty($request['purchase_date'])) ? $request['purchase_date'] : null),
                    "clinic_name" => $request['clinic_name'] ?? '',
                    "doctor_code" =>  $request['doctor_code'] ?? '',         
                    "allocated_xray" =>  $request['allocated_xray'] ?? '',
                    "xray_code" =>  $request['xray_code'] ?? ''
                ]);
        
                WorkerInsuranceDetails::create([
                    "worker_id" => $worker['id'],
                    "ig_policy_number" => $request['ig_policy_number'] ?? '',
                    "ig_policy_number_valid_until" => ((isset($request['ig_policy_number_valid_until']) && !empty($request['ig_policy_number_valid_until'])) ? $request['ig_policy_number_valid_until'] : null),
                    "hospitalization_policy_number" =>  $request['hospitalization_policy_number'] ?? '',         
                    "hospitalization_policy_number_valid_until" =>  ((isset($request['hospitalization_policy_number_valid_until']) && !empty($request['hospitalization_policy_number_valid_until'])) ? $request['hospitalization_policy_number_valid_until'] : null),
                    "insurance_expiry_date" => ((isset($request['insurance_expiry_date']) && !empty($request['insurance_expiry_date'])) ? $request['insurance_expiry_date'] : null)
                ]);
        
                WorkerBankDetails::create([
                    "worker_id" => $worker['id'],
                    "bank_name" => $request['bank_name'] ?? '',
                    "account_number" => $request['account_number'] ?? '',
                    "socso_number" =>  $request['socso_number'] ?? ''
                ]);

                $checkCallingVisa = DirectRecruitmentCallingVisaStatus::where('application_id', $this->workerParameter['application_id'])
                ->where('onboarding_country_id', $this->workerParameter['onboarding_country_id'])
                ->where('agent_id', $this->workerParameter['agent_id'])->get()->toArray();

                if(isset($checkCallingVisa) && count($checkCallingVisa) == 0 ){
                    $callingVisaStatus = DirectRecruitmentCallingVisaStatus::create([
                        'application_id' => $this->workerParameter['application_id'] ?? 0,
                        'onboarding_country_id' => $this->workerParameter['onboarding_country_id'] ?? 0,
                        'agent_id' => $this->workerParameter['agent_id'] ?? 0,
                        'item' => 'Calling Visa Status',
                        'updated_on' => Carbon::now(),
                        'status' => 1
                    ]);
                }

                $checkWorkerStatus = WorkerStatus::where('application_id', $this->workerParameter['application_id'])
                ->where('onboarding_country_id', $this->workerParameter['onboarding_country_id'])
                ->get()->toArray();

                if(isset($checkWorkerStatus) && count($checkWorkerStatus) > 0 ){
                    WorkerStatus::where([
                        'application_id' => $this->workerParameter['application_id'],
                        'onboarding_country_id' => $this->workerParameter['onboarding_country_id']
                    ])->update(['updated_on' => Carbon::now()]);
                } else {
                    $workerStatus = WorkerStatus::create([
                        'application_id' => $this->workerParameter['application_id'] ?? 0,
                        'onboarding_country_id' => $this->workerParameter['onboarding_country_id'] ?? 0,
                        'item' => 'Worker Biodata',
                        'updated_on' => Carbon::now(),
                        'status' => 1
                    ]);
                }

                $onboardingCountry = DirectRecruitmentOnboardingCountry::findOrFail($this->workerParameter['onboarding_country_id']);

                $onboardingCountry->onboarding_status =  4;
                $onboardingCountry->save();                
    
                Log::info('Worker inserted -  '.$worker['id']);
                DB::table('worker_bulk_upload')->where('id', $this->bulkUpload->id)->increment('total_success');
                $successFlag = 1;
                $comments .= ' SUCCESS - worker imported';
            }else{
                DB::table('worker_bulk_upload')->where('id', $this->bulkUpload->id)->increment('total_failure');
                Log::info('ERROR - worker import failed  due to '.$comments);
            }
        }else{
            DB::table('worker_bulk_upload')->where('id', $this->bulkUpload->id)->increment('total_failure');
            Log::info('ERROR - required params are empty');
            $comments .= ' ERROR - ' . $validationError;
        }
        
        $this->insertRecord($comments, 1, $successFlag);
    }
    /**
     * @param $workers
     * @return array
     */
    public function createValidation($workers): array
    {
        $emptyFields = [];
        $i=0;
        foreach($workers as $key => $worker) {
            // Log::info($key);
            if(empty($worker)) {
                $emptyFields[$i++] = " " . $key . " is required";
            }
        }
        return $emptyFields;
    }
    /**
     * @param string $comments
     * @param int $status
     * @param int $successFlag
     */
    public function insertRecord($comments = '', $status = 1, $successFlag): void
    {
        BulkUploadRecords::create(
            [
                'bulk_upload_id' => $this->bulkUpload->id,
                'parameter' => json_encode($this->workerParameter),
                'comments' => $comments,
                'status' => $status,
                'success_flag' => $successFlag
            ]
        );
    }
}
