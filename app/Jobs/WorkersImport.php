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
use App\Models\OnboardingCountriesKSMReferenceNumber;
use App\Models\Levy;
use App\Services\ManageWorkersServices;
use App\Services\DatabaseConnectionServices;
use App\Models\Agent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;

class WorkersImport extends Job
{
    private $dbName;
    private $parameters;
    private $bulkUpload;
    private $workerParameter;
    Private $workerNonMandatory;

    /**
     * Create a new job instance.
     *
     * @param $dbName
     * @param $workerParameter
     * @param $parameters
     * @param $bulkUpload
     * @param $workerNonMandatory
     */
    public function __construct($dbName, $workerParameter, $bulkUpload, $workerNonMandatory)
    {
        $this->dbName = $dbName;
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
            'agent_code' => 'required',
            'application_id' => 'required',
            'name' => 'required|regex:/^[a-zA-Z ]*$/|max:255',
            'date_of_birth' => 'required|date|date_format:Y-m-d',
            'gender' => 'required|regex:/^[a-zA-Z]*$/|max:15',                        
            'passport_number' => 'required|regex:/^[a-zA-Z0-9]*$/|unique:workers',
            'passport_valid_until' => 'required|date|date_format:Y-m-d',
            'address' => 'required',
            'state' => 'required|regex:/^[a-zA-Z ]*$/|max:150',
            'kin_name' => 'required|regex:/^[a-zA-Z ]*$/|max:255',
            'kin_relationship' => 'required',
            'kin_contact_number' => 'required|regex:/^[0-9]+$/',
            'ksm_reference_number' => 'required',
            'bio_medical_reference_number' => 'required|regex:/^[a-zA-Z0-9]*$/|max:255',
            'bio_medical_valid_until' => 'required|date|date_format:Y-m-d',
            'company_id' => 'required'
        ];
    }

    /**
     * Execute the job.
     *
     * @param DatabaseConnectionServices $databaseConnectionServices
     * @return void
     * @throws \JsonException
     */
    public function handle(DatabaseConnectionServices $databaseConnectionServices): void
    { 

        Log::info('Worker insert - started '.$this->dbName);
        $databaseConnectionServices->dbConnectQueue($this->dbName);
        Log::info('Worker insert - started ');

        $comments = '';
        $countryQuotaError = 0;
        $ksmReferenceNumberQuotaError = 0;
        $agentWorkerCountError = 0;
        $successFlag = 0;
        $agentKSMError = 0;
        $agentError = 0;
        $kinRelationshipError = 0;
        $agentCodeCheckError = 0;

        $validationError = [];
        $validator = Validator::make($this->workerParameter, $this->formatValidation());
        if($validator->fails()) {
            $validationError = str_replace(".","", implode(",",$validator->messages()->all()));
        }

        if(empty($validationError)) {

            $kinRelationship = DB::table('kin_relationship')->where('name', $this->workerParameter['kin_relationship'])->first('id');

            if(empty($kinRelationship)) {
                $kinRelationshipError = 1;
                $comments .= ' ERROR - kin relationship is mis-matched';
            }
            $kinRelationshipId = $kinRelationship->id ?? 0;
            Log::info('Row Data - realtionship - ' . print_r($kinRelationshipId, true));

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
                    Log::info('Row Data - KSM Reference Number Error - ');   
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

        // $approvedCount = Levy::where('application_id', $this->workerParameter['application_id'])
        //                      ->where('new_ksm_reference_number', $this->workerParameter['ksm_reference_number'])
        //                      ->first('approved_quota');

        // if(isset($approvedCount['approved_quota'])){
        //     Log::info('levy approved count - ' . $approvedCount['approved_quota']);
        // }

        $approvedCount = OnboardingCountriesKSMReferenceNumber::where('application_id', $this->workerParameter['application_id'])
                            ->where('onboarding_country_id', $this->workerParameter['onboarding_country_id'])
                            ->where('ksm_reference_number', $this->workerParameter['ksm_reference_number'])
                            ->sum('quota');

        if(isset($approvedCount)){
            Log::info('Onboarding KSM approved count - ' . $approvedCount);
        }
        
        $ksmReferenceNumberCount = DB::table('directrecruitment_workers')
        ->leftjoin('worker_visa', 'directrecruitment_workers.worker_id', '=', 'worker_visa.worker_id')
        ->leftjoin('workers', 'workers.id', '=', 'worker_visa.worker_id')
        ->where([
            ['directrecruitment_workers.application_id', $this->workerParameter['application_id']],
            ['directrecruitment_workers.onboarding_country_id', $this->workerParameter['onboarding_country_id']],
            ['workers.cancel_status', 0],
            ['worker_visa.ksm_reference_number', $this->workerParameter['ksm_reference_number']],
        ])
        ->whereIn('workers.directrecruitment_status', Config::get('services.DIRECT_RECRUITMENT_WORKER_STATUS'))
        ->count('directrecruitment_workers.worker_id');

        Log::info('ksm reference number count - ' . $ksmReferenceNumberCount);

        if(isset($approvedCount) && ($ksmReferenceNumberCount >= $approvedCount)) {
            $ksmReferenceNumberQuotaError = 1;
            $comments .= ' ERROR - ksm reference number quota cannot exceeded';
        }

        /*$agentDataCheck = DirectRecruitmentOnboardingAgent::where('application_id', $this->workerParameter['application_id'])
        ->where('onboarding_country_id', $this->workerParameter['onboarding_country_id'])
        ->where('ksm_reference_number', $this->workerParameter['ksm_reference_number'])
        ->select('id')
        ->get();

        $agentData = array();
        foreach ($agentDataCheck as $key => $agentId) {
            Log::info('ksm result - ' . ($agentId->id));
            $agentData[$key] = $agentId->id;
        }
     
        if(isset($agentData) && !empty($agentData)){
            if(!in_array($this->workerParameter['agent_id'], $agentData)){
                Log::info('Row Data -  The On Boarding Agent Id error - ' . print_r($this->workerParameter['agent_id'], true));   
                $agentError = 1; 
                $comments .= 'ERROR - The On Boarding Agent Id is mis-matched';
            }
        }*/

        $agentCodeCheck = Agent::where('agent_code', $this->workerParameter['agent_code'])->first('id');
        $agentId = $agentCodeCheck->id ?? 0;
        if(empty($agentCodeCheck)) {
            $agentCodeCheckError = 1;
            $comments .= ' ERROR - agent code is mis-matched';
        }
        Log::info('Row Data - agent id - ' . print_r($agentId, true));

        $agentCheck = DirectRecruitmentOnboardingAgent::where('application_id', $this->workerParameter['application_id'])
        ->where('onboarding_country_id', $this->workerParameter['onboarding_country_id'])
        ->where('ksm_reference_number', $this->workerParameter['ksm_reference_number'])
        ->where('agent_id', $agentId)
        ->first('id');
        $onboardingAgentId = $agentCheck->id ?? 0;
        
        if(empty($agentCheck)) {
            $agentKSMError = 1;
            $comments .= ' ERROR - The KSM reference number not belongs to this Agent';
        }

        Log::info('Row Data - onboarding agent id  - ' . print_r($onboardingAgentId, true));

        //$agentDetails = DirectRecruitmentOnboardingAgent::findOrFail($onboardingAgentId);
        $agentDetails = DirectRecruitmentOnboardingAgent::where('id', $onboardingAgentId)->first('quota');

        $agentWorkerCount = DB::table('directrecruitment_workers')
        ->leftjoin('worker_visa', 'directrecruitment_workers.worker_id', '=', 'worker_visa.worker_id')
        ->leftjoin('workers', 'workers.id', '=', 'worker_visa.worker_id')
        ->where([
            ['directrecruitment_workers.application_id', $this->workerParameter['application_id']],
            ['directrecruitment_workers.onboarding_country_id', $this->workerParameter['onboarding_country_id']],
            ['directrecruitment_workers.agent_id', $onboardingAgentId],
            ['workers.cancel_status', 0]
        ])
        ->whereIn('workers.directrecruitment_status', Config::get('services.DIRECT_RECRUITMENT_WORKER_STATUS'))
        ->count('directrecruitment_workers.worker_id');

        if(isset($agentDetails->quota) && $agentWorkerCount >= $agentDetails->quota) {
            $agentWorkerCountError = 1;
            $comments .= ' ERROR - agent worker quota count cannot exceeded'; 
        }
            if($kinRelationshipError == 0 && $workerCount == 0 && $ksmError == 0 && $countryQuotaError == 0 && $ksmReferenceNumberQuotaError == 0 && $agentWorkerCountError == 0 && $agentKSMError == 0 && $agentCodeCheckError == 0){

                $applicationDetails = DirectrecruitmentApplications::findOrFail($this->workerParameter['application_id']);
                $prospect_id = $applicationDetails->crm_prospect_id;

                $worker = Workers::create([            
                    'name' => $this->workerParameter['name'] ?? '',
                    'gender' => ucfirst($this->workerParameter['gender']) ?? '',
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
                    'agent_id' => $onboardingAgentId ?? 0,
                    'application_id' => $this->workerParameter['application_id'] ?? 0,
                    'created_by'    => $this->workerParameter['created_by'] ?? 0,
                    'modified_by'   => $this->workerParameter['created_by'] ?? 0 ,
                    'created_at'    => Carbon::now()->toDateTimeString(),
                    'updated_at'    => Carbon::now()->toDateTimeString()         
                ]);
    
                WorkerKin::create([
                    "worker_id" => $worker['id'],
                    "kin_name" => $this->workerParameter['kin_name'] ?? '',
                    "kin_relationship_id" => $kinRelationship->id ?? 0,
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
                ->where('agent_id', $onboardingAgentId)->get()->toArray();

                if(isset($checkCallingVisa) && count($checkCallingVisa) == 0 ){
                    $callingVisaStatus = DirectRecruitmentCallingVisaStatus::create([
                        'application_id' => $this->workerParameter['application_id'] ?? 0,
                        'onboarding_country_id' => $this->workerParameter['onboarding_country_id'] ?? 0,
                        'agent_id' => $onboardingAgentId ?? 0,
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
        
        $this->insertRecord($comments, 1, $successFlag, $this->workerParameter['company_id']);
    }
    /**
     * @param string $comments
     * @param int $status
     * @param int $successFlag
     * @param int $companyId
     */
    public function insertRecord($comments = '', $status = 1, $successFlag, $companyId): void
    {
        BulkUploadRecords::create(
            [
                'bulk_upload_id' => $this->bulkUpload->id,
                'parameter' => json_encode($this->workerParameter),
                'comments' => $comments,
                'status' => $status,
                'success_flag' => $successFlag,
                'company_id' => $companyId
            ]
        );
    }
}
