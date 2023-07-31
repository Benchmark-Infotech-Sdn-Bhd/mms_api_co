<?php

namespace App\Services;

use App\Models\Workers;
use App\Models\WorkerAttachments;
use App\Models\WorkerKin;
use App\Models\WorkerVisa;
use App\Models\WorkerVisaAttachments;
use App\Models\WorkerBioMedical;
use App\Models\WorkerBioMedicalAttachments;
use App\Models\WorkerFomema;
use App\Models\WorkerInsuranceDetails;
use App\Models\WorkerBankDetails;
use App\Models\KinRelationship;
use App\Models\DirectRecruitmentCallingVisaStatus;
use App\Models\DirectRecruitmentOnboardingAgent;
use App\Models\WorkerStatus;
use App\Models\DirectrecruitmentWorkers;
use App\Services\DirectRecruitmentOnboardingCountryServices;
use App\Services\ValidationServices;
use Illuminate\Support\Facades\Config;
use App\Services\AuthServices;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;

class WorkersServices
{
    private Workers $workers;
    private WorkerAttachments $workerAttachments;
    private WorkerKin $workerKin;
    private WorkerVisa $workerVisa;
    private WorkerVisaAttachments $workerVisaAttachments;
    private WorkerBioMedical $workerBioMedical;
    private WorkerBioMedicalAttachments $workerBioMedicalAttachments;
    private WorkerFomema $workerFomema;
    private WorkerInsuranceDetails $workerInsuranceDetails;
    private WorkerBankDetails $workerBankDetails;
    private KinRelationship $kinRelationship;
    private DirectRecruitmentCallingVisaStatus $directRecruitmentCallingVisaStatus;
    private DirectRecruitmentOnboardingAgent $directRecruitmentOnboardingAgent;
    private WorkerStatus $workerStatus;
    private DirectRecruitmentOnboardingCountryServices $directRecruitmentOnboardingCountryServices;
    private ValidationServices $validationServices;
    private AuthServices $authServices;
    private Storage $storage;
    private DirectrecruitmentWorkers $directrecruitmentWorkers;
    /**
     * WorkersServices constructor.
     * @param Workers $workers
     * @param WorkerAttachments $workerAttachments
     * @param WorkerKin $workerKin
     * @param WorkerVisa $workerVisa
     * @param WorkerVisaAttachments $workerVisaAttachments
     * @param WorkerBioMedical $workerBioMedical
     * @param WorkerBioMedicalAttachments $workerBioMedicalAttachments
     * @param WorkerFomema $workerFomema
     * @param WorkerInsuranceDetails $workerInsuranceDetails
     * @param WorkerBankDetails $workerBankDetails
     * @param KinRelationship $kinRelationship
     * @param DirectRecruitmentCallingVisaStatus $directRecruitmentCallingVisaStatus
     * @param DirectRecruitmentOnboardingAgent $directRecruitmentOnboardingAgent
     * @param WorkerStatus $workerStatus
     * @param DirectRecruitmentOnboardingCountryServices $directRecruitmentOnboardingCountryServices;
     * @param ValidationServices $validationServices
     * @param AuthServices $authServices
     * @param Storage $storage
     * @param DirectrecruitmentWorkers $directrecruitmentWorkers;
     */
    public function __construct(
            Workers                                     $workers,
            WorkerAttachments                           $workerAttachments,
            WorkerKin                                   $workerKin,
            WorkerVisa                                  $workerVisa,
            WorkerVisaAttachments                       $workerVisaAttachments,
            WorkerBioMedical                            $workerBioMedical,
            WorkerBioMedicalAttachments                 $workerBioMedicalAttachments,
            WorkerFomema                                $workerFomema,
            WorkerInsuranceDetails                      $workerInsuranceDetails,
            WorkerBankDetails                           $workerBankDetails,
            KinRelationship                             $kinRelationship,
            DirectRecruitmentCallingVisaStatus          $directRecruitmentCallingVisaStatus,
            DirectRecruitmentOnboardingAgent            $directRecruitmentOnboardingAgent,
            WorkerStatus                                $workerStatus,
            DirectRecruitmentOnboardingCountryServices  $directRecruitmentOnboardingCountryServices, 
            ValidationServices                          $validationServices,
            AuthServices                                $authServices,
            Storage                                     $storage,
            DirectrecruitmentWorkers                    $directrecruitmentWorkers
    )
    {
        $this->workers = $workers;
        $this->workerAttachments = $workerAttachments;
        $this->workerKin = $workerKin;
        $this->workerVisa = $workerVisa;
        $this->workerVisaAttachments = $workerVisaAttachments;
        $this->workerBioMedical = $workerBioMedical;
        $this->workerBioMedicalAttachments = $workerBioMedicalAttachments;
        $this->workerFomema = $workerFomema;
        $this->workerInsuranceDetails = $workerInsuranceDetails;
        $this->workerBankDetails = $workerBankDetails;
        $this->kinRelationship = $kinRelationship;
        $this->workerStatus = $workerStatus;
        $this->validationServices = $validationServices;
        $this->directRecruitmentOnboardingCountryServices = $directRecruitmentOnboardingCountryServices;
        $this->authServices = $authServices;
        $this->storage = $storage;
        $this->directRecruitmentCallingVisaStatus = $directRecruitmentCallingVisaStatus;
        $this->directRecruitmentOnboardingAgent = $directRecruitmentOnboardingAgent;
        $this->directrecruitmentWorkers = $directrecruitmentWorkers;
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
        if(!($this->validationServices->validate($request->toArray(),$this->workers->rules))){
            return [
              'validate' => $this->validationServices->errors()
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

        $worker = $this->workers->create([
            'name' => $request['name'] ?? '',
            'gender' => $request['gender'] ?? '',
            'date_of_birth' => $request['date_of_birth'] ?? '',
            'passport_number' => $request['passport_number'] ?? '',
            'passport_valid_until' => $request['passport_valid_until'] ?? '',
            'fomema_valid_until' => ((isset($request['fomema_valid_until']) && !empty($request['fomema_valid_until'])) ? $request['fomema_valid_until'] : null),
            'status' => 1,
            'address' => $request['address'] ?? '',
            'city' => $request['city'] ?? '',
            'state' => $request['state'] ?? '',
            'created_by'    => $params['created_by'] ?? 0,
            'modified_by'   => $params['created_by'] ?? 0
        ]);

        $directrecruitmentWorkers = $this->directrecruitmentWorkers::create([
            "worker_id" => $worker['id'],
            'onboarding_country_id' => $request['onboarding_country_id'] ?? 0,
            'agent_id' => $request['agent_id'] ?? 0,
            'application_id' => $request['application_id'] ?? 0,
            'created_by'    => $params['created_by'] ?? 0,
            'modified_by'   => $params['created_by'] ?? 0   
        ]);

        if (request()->hasFile('fomema_attachment')){
            foreach($request->file('fomema_attachment') as $file){
                $fileName = $file->getClientOriginalName();
                $filePath = '/workerbiodata/'.$worker['id'].'/fomema/'. $fileName; 
                $linode = $this->storage::disk('linode');
                $linode->put($filePath, file_get_contents($file));
                $fileUrl = $this->storage::disk('linode')->url($filePath);
                $this->workerAttachments::create([
                        "file_id" => $worker['id'],
                        "file_name" => $fileName,
                        "file_type" => 'FOMEMA',
                        "file_url" =>  $fileUrl
                    ]);  
            }
        }

        if (request()->hasFile('passport_attachment')){
            foreach($request->file('passport_attachment') as $file){
                $fileName = $file->getClientOriginalName();
                $filePath = '/workerbiodata/'.$worker['id'].'/passport/' . $fileName; 
                $linode = $this->storage::disk('linode');
                $linode->put($filePath, file_get_contents($file));
                $fileUrl = $this->storage::disk('linode')->url($filePath);
                $this->workerAttachments::create([
                        "file_id" => $worker['id'],
                        "file_name" => $fileName,
                        "file_type" => 'PASSPORT',
                        "file_url" =>  $fileUrl         
                    ]);  
            }
        }

        if (request()->hasFile('profile_picture')){
            foreach($request->file('profile_picture') as $file){
                $fileName = $file->getClientOriginalName();
                $filePath = '/workerbiodata/'.$worker['id'].'/profile/' . $fileName; 
                $linode = $this->storage::disk('linode');
                $linode->put($filePath, file_get_contents($file));
                $fileUrl = $this->storage::disk('linode')->url($filePath);
                $this->workerAttachments::create([
                        "file_id" => $worker['id'],
                        "file_name" => $fileName,
                        "file_type" => 'PROFILE',
                        "file_url" =>  $fileUrl         
                    ]);  
            }
        }

        $this->workerKin::create([
            "worker_id" => $worker['id'],
            "kin_name" => $request['kin_name'] ?? '',
            "kin_relationship_id" => $request['kin_relationship_id'] ?? '',
            "kin_contact_number" =>  $request['kin_contact_number'] ?? ''         
        ]);

        $workerVisa = $this->workerVisa::create([
            "worker_id" => $worker['id'],
            "ksm_reference_number" => $request['ksm_reference_number'],
            "calling_visa_reference_number" => $request['calling_visa_reference_number'] ?? '',
            "calling_visa_valid_until" =>  ((isset($request['calling_visa_valid_until']) && !empty($request['calling_visa_valid_until'])) ? $request['calling_visa_valid_until'] : null),         
            "entry_visa_valid_until" =>  ((isset($request['entry_visa_valid_until']) && !empty($request['entry_visa_valid_until'])) ? $request['entry_visa_valid_until'] : null),
            "work_permit_valid_until" =>  ((isset($request['work_permit_valid_until']) && !empty($request['work_permit_valid_until'])) ? $request['work_permit_valid_until'] : null)
        ]);

        if (request()->hasFile('worker_visa_attachment')){
            foreach($request->file('worker_visa_attachment') as $file){
                $fileName = $file->getClientOriginalName();
                $filePath = '/workerbiodata/'.$worker['id'].'/workerVisa/' . $fileName; 
                $linode = $this->storage::disk('linode');
                $linode->put($filePath, file_get_contents($file));
                $fileUrl = $this->storage::disk('linode')->url($filePath);
                $this->workerVisaAttachments::create([
                        "file_id" => $workerVisa['id'],
                        "file_name" => $fileName,
                        "file_type" => 'WORKPERMIT',
                        "file_url" =>  $fileUrl         
                    ]);  
            }
        }

        $workerBioMedical = $this->workerBioMedical::create([
            "worker_id" => $worker['id'],
            "bio_medical_reference_number" => $request['bio_medical_reference_number'],
            "bio_medical_valid_until" => $request['bio_medical_valid_until'],
        ]);

        if (request()->hasFile('worker_bio_medical_attachment')){
            foreach($request->file('worker_bio_medical_attachment') as $file){
                $fileName = $file->getClientOriginalName();
                $filePath = '/workerbiodata/'.$worker['id'].'/workerBioMedical/' . $fileName; 
                $linode = $this->storage::disk('linode');
                $linode->put($filePath, file_get_contents($file));
                $fileUrl = $this->storage::disk('linode')->url($filePath);
                $this->workerBioMedicalAttachments::create([
                        "file_id" => $workerBioMedical['id'],
                        "file_name" => $fileName,
                        "file_type" => 'BIOMEDICAL',
                        "file_url" =>  $fileUrl         
                    ]);  
            }
        }

        $workerFomema = $this->workerFomema::create([
            "worker_id" => $worker['id'],
            "purchase_date" => ((isset($request['purchase_date']) && !empty($request['purchase_date'])) ? $request['purchase_date'] : null),
            "clinic_name" => $request['clinic_name'] ?? '',
            "doctor_code" =>  $request['doctor_code'] ?? '',         
            "allocated_xray" =>  $request['allocated_xray'] ?? '',
            "xray_code" =>  $request['xray_code'] ?? ''
        ]);

        $workerInsuranceDetails = $this->workerInsuranceDetails::create([
            "worker_id" => $worker['id'],
            "ig_policy_number" => $request['ig_policy_number'] ?? '',
            "ig_policy_number_valid_until" => ((isset($request['ig_policy_number_valid_until']) && !empty($request['ig_policy_number_valid_until'])) ? $request['ig_policy_number_valid_until'] : null),
            "hospitalization_policy_number" =>  $request['hospitalization_policy_number'] ?? '',         
            "hospitalization_policy_number_valid_until" =>  ((isset($request['hospitalization_policy_number_valid_until']) && !empty($request['hospitalization_policy_number_valid_until'])) ? $request['hospitalization_policy_number_valid_until'] : null)
        ]);

        $workerBankDetails = $this->workerBankDetails::create([
            "worker_id" => $worker['id'],
            "bank_name" => $request['bank_name'] ?? '',
            "account_number" => $request['account_number'] ?? '',
            "socso_number" =>  $request['socso_number'] ?? ''
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

        return $worker;
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

        if(!($this->validationServices->validate($request->toArray(),$this->workers->rulesForUpdation($request['id'])))){
            return [
                'validate' => $this->validationServices->errors()
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

        $worker = $this->workers->with('directrecruitmentWorkers', 'workerAttachments', 'workerKin', 'workerVisa', 'workerBioMedical', 'workerFomema', 'workerInsuranceDetails', 'workerBankDetails')->findOrFail($request['id']);

        $worker->name = $request['name'] ?? $worker->name;
        $worker->gender = $request['gender'] ?? $worker->gender;
        $worker->date_of_birth = $request['date_of_birth'] ?? $worker->date_of_birth;
        $worker->passport_number = $request['passport_number'] ?? $worker->passport_number;
        $worker->passport_valid_until = $request['passport_valid_until'] ?? $worker->passport_valid_until;

        $worker->fomema_valid_until = $request['fomema_valid_until'] ?? $worker->fomema_valid_until;

        $worker->address = $request['address'] ?? $worker->address;
        $worker->city = $request['city'] ?? $worker->city;
        $worker->state = $request['state'] ?? $worker->state;
        $worker->created_by = $request['created_by'] ?? $worker->created_by;
        $worker->modified_by = $params['modified_by'];

        # directrecuriment Worker
        $worker->directrecruitmentWorkers->onboarding_country_id = $request['onboarding_country_id'] ?? $worker->directrecruitmentWorkers->onboarding_country_id;
        $worker->directrecruitmentWorkers->agent_id = $request['agent_id'] ?? $worker->directrecruitmentWorkers->agent_id;
        $worker->directrecruitmentWorkers->application_id = $request['application_id'] ?? $worker->directrecruitmentWorkers->application_id;

        # Worker Kin details
        $worker->workerKin->kin_name = $request['kin_name'] ?? $worker->workerKin->kin_name;
        $worker->workerKin->kin_relationship_id = $request['kin_relationship_id'] ?? $worker->workerKin->kin_relationship_id;
        $worker->workerKin->kin_contact_number = $request['kin_contact_number'] ?? $worker->workerKin->kin_contact_number;

        # Worker Visa details
        $worker->workerVisa->ksm_reference_number = $request['ksm_reference_number'] ?? $worker->workerVisa->ksm_reference_number;
        $worker->workerVisa->calling_visa_reference_number = $request['calling_visa_reference_number'] ?? $worker->workerVisa->calling_visa_reference_number;
        $worker->workerVisa->calling_visa_valid_until = $request['calling_visa_valid_until'] ?? $worker->workerVisa->calling_visa_valid_until;
        $worker->workerVisa->entry_visa_valid_until = $request['entry_visa_valid_until'] ?? $worker->workerVisa->entry_visa_valid_until;
        $worker->workerVisa->work_permit_valid_until = $request['work_permit_valid_until'] ?? $worker->workerVisa->work_permit_valid_until;

        # Worker Bio Medical details
        $worker->workerBioMedical->bio_medical_reference_number = $request['bio_medical_reference_number'] ?? $worker->workerBioMedical->bio_medical_reference_number;
        $worker->workerBioMedical->bio_medical_valid_until = $request['bio_medical_valid_until'] ?? $worker->workerBioMedical->bio_medical_valid_until;

        # Worker Fomema details
        $worker->workerFomema->purchase_date = $request['purchase_date'] ?? $worker->workerFomema->purchase_date;
        $worker->workerFomema->clinic_name = $request['clinic_name'] ?? $worker->workerFomema->clinic_name;
        $worker->workerFomema->doctor_code = $request['doctor_code'] ?? $worker->workerFomema->doctor_code;
        $worker->workerFomema->allocated_xray = $request['allocated_xray'] ?? $worker->workerFomema->allocated_xray;
        $worker->workerFomema->xray_code = $request['xray_code'] ?? $worker->workerFomema->xray_code;

        # Worker Insurance details
        $worker->workerInsuranceDetails->ig_policy_number = $request['ig_policy_number'] ?? $worker->workerInsuranceDetails->ig_policy_number;
        $worker->workerInsuranceDetails->ig_policy_number_valid_until = $request['ig_policy_number_valid_until'] ?? $worker->workerInsuranceDetails->ig_policy_number_valid_until;
        $worker->workerInsuranceDetails->hospitalization_policy_number = $request['hospitalization_policy_number'] ?? $worker->workerInsuranceDetails->hospitalization_policy_number;
        $worker->workerInsuranceDetails->hospitalization_policy_number_valid_until = $request['hospitalization_policy_number_valid_until'] ?? $worker->workerInsuranceDetails->hospitalization_policy_number_valid_until;

        # Worker Bank details
        $worker->workerBankDetails->bank_name = $request['bank_name'] ?? $worker->workerBankDetails->bank_name;
        $worker->workerBankDetails->account_number = $request['account_number'] ?? $worker->workerBankDetails->account_number;
        $worker->workerBankDetails->socso_number = $request['socso_number'] ?? $worker->workerBankDetails->socso_number;
        
        $worker->workerKin->save();
        $worker->workerVisa->save();
        $worker->workerBioMedical->save();
        $worker->workerFomema->save();
        $worker->workerInsuranceDetails->save();
        $worker->workerBankDetails->save();
        $worker->save();

        if (request()->hasFile('fomema_attachment')){

            $this->workerAttachments->where('file_id', $request['id'])->where('file_type', 'FOMEMA')->delete();

            foreach($request->file('fomema_attachment') as $file){
                $fileName = $file->getClientOriginalName();
                $filePath = '/workerbiodata/'.$request['id'].'/fomema/'. $fileName; 
                $linode = $this->storage::disk('linode');
                $linode->put($filePath, file_get_contents($file));
                $fileUrl = $this->storage::disk('linode')->url($filePath);
                $this->workerAttachments::create([
                    "file_id" => $request['id'],
                    "file_name" => $fileName,
                    "file_type" => 'FOMEMA',
                    "file_url" =>  $fileUrl         
                ]);  
            }
        }

        if (request()->hasFile('passport_attachment')){

            $this->workerAttachments->where('file_id', $request['id'])->where('file_type', 'PASSPORT')->delete();

            foreach($request->file('passport_attachment') as $file){
                $fileName = $file->getClientOriginalName();
                $filePath = '/workerbiodata/'.$request['id'].'/passport/' . $fileName;
                $linode = $this->storage::disk('linode');
                $linode->put($filePath, file_get_contents($file));
                $fileUrl = $this->storage::disk('linode')->url($filePath);
                $this->workerAttachments::create([
                    "file_id" => $request['id'],
                    "file_name" => $fileName,
                    "file_type" => 'PASSPORT',
                    "file_url" =>  $fileUrl         
                ]);  
            }
        }

        if (request()->hasFile('profile_picture')){

            $this->workerAttachments->where('file_id', $request['id'])->where('file_type', 'PROFILE')->delete();

            foreach($request->file('profile_picture') as $file){
                $fileName = $file->getClientOriginalName();
                $filePath = '/workerbiodata/'.$request['id'].'/profile/' . $fileName;
                $linode = $this->storage::disk('linode');
                $linode->put($filePath, file_get_contents($file));
                $fileUrl = $this->storage::disk('linode')->url($filePath);
                $this->workerAttachments::create([
                    "file_id" => $request['id'],
                    "file_name" => $fileName,
                    "file_type" => 'PROFILE',
                    "file_url" =>  $fileUrl         
                ]);  
            }
        }

        if (request()->hasFile('worker_visa_attachment')){

            $this->workerVisaAttachments->where('file_id', $worker->workerVisa->id)->where('file_type', 'WORKPERMIT')->delete();

            foreach($request->file('worker_visa_attachment') as $file){
                $fileName = $file->getClientOriginalName();
                $filePath = '/workerbiodata/'.$request['id'].'/workerVisa/' . $fileName;
                $linode = $this->storage::disk('linode');
                $linode->put($filePath, file_get_contents($file));
                $fileUrl = $this->storage::disk('linode')->url($filePath);
                $this->workerVisaAttachments::create([
                    "file_id" => $worker->workerVisa->id,
                    "file_name" => $fileName,
                    "file_type" => 'WORKPERMIT',
                    "file_url" =>  $fileUrl         
                ]);  
            }
        }

        if (request()->hasFile('worker_bio_medical_attachment')){

            $this->workerBioMedicalAttachments->where('file_id', $worker->workerBioMedical->id)->where('file_type', 'BIOMEDICAL')->delete();

            foreach($request->file('worker_bio_medical_attachment') as $file){
                $fileName = $file->getClientOriginalName();
                $filePath = '/workerbiodata/'.$request['id'].'/workerBioMedical/' . $fileName;
                $linode = $this->storage::disk('linode');
                $linode->put($filePath, file_get_contents($file));
                $fileUrl = $this->storage::disk('linode')->url($filePath);
                $this->workerBioMedicalAttachments::create([
                    "file_id" => $worker->workerBioMedical->id,
                    "file_name" => $fileName,
                    "file_type" => 'BIOMEDICAL',
                    "file_url" =>  $fileUrl         
                ]);  
            }
        }

        $this->workerStatus->where([
            'application_id' => $request['application_id'],
            'onboarding_country_id' => $request['onboarding_country_id']
        ])->update(['updated_on' => Carbon::now(), 'modified_by' => $params['modified_by']]);

        return true;
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
        ->join('worker_bio_medical', 'workers.id', '=', 'worker_bio_medical.worker_id')
        ->leftjoin('worker_arrival', 'workers.id', '=', 'worker_arrival.worker_id')
        ->leftjoin('directrecruitment_workers', 'workers.id', '=', 'directrecruitment_workers.worker_id')
        ->where('directrecruitment_workers.application_id', $request['application_id'])
        ->where('directrecruitment_workers.onboarding_country_id', $request['onboarding_country_id'])
        ->where('directrecruitment_workers.agent_id', $request['agent_id'])
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
        })->select('workers.id','workers.name','workers.date_of_birth','workers.gender','workers.passport_number','workers.passport_valid_until','workers.address','workers.state','worker_kin.kin_name','worker_kin.kin_relationship_id','worker_kin.kin_contact_number','worker_visa.ksm_reference_number','worker_bio_medical.bio_medical_reference_number','worker_bio_medical.bio_medical_valid_until')
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
        ->select('workers.id','workers.name')
        ->orderBy('workers.created_at','DESC')->get();
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
}
