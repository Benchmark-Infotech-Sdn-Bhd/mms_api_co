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
use App\Models\WorkerBulkUpload;
use App\Models\DirectrecruitmentWorkers;
use App\Services\DirectRecruitmentOnboardingCountryServices;
use App\Services\ValidationServices;
use Illuminate\Support\Facades\Config;
use App\Services\AuthServices;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Imports\CommonWorkerImport;
use Maatwebsite\Excel\Facades\Excel;

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
    private WorkerBulkUpload $workerBulkUpload;

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
     * @param WorkerBulkUpload $workerBulkUpload
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
            DirectrecruitmentWorkers                    $directrecruitmentWorkers,
            WorkerBulkUpload                            $workerBulkUpload
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
        $this->workerBulkUpload = $workerBulkUpload;
    }
    /**
     * @return array
     */
    public function assignWorkerValidation(): array
    {
        return
            [
                'application_id' => 'required',
                'onboarding_country_id' => 'required',
                'agent_id' => 'required'
            ];
    }
    /**
     * @return array
     */
    public function addAttachmentValidation(): array
    {
        return [
            'worker_id' => 'required',
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
        $params['company_id'] = $user['company_id'];
        if(!($this->validationServices->validate($request->toArray(),$this->workers->rules))){
            return [
              'validate' => $this->validationServices->errors()
            ];
        }

        $worker = $this->workers->create([
            'crm_prospect_id' => $request['crm_prospect_id'] ?? 0,
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
            'crm_prospect_id' => $request['crm_prospect_id'] ?? NULL,
            'created_by'    => $params['created_by'] ?? 0,
            'modified_by'   => $params['created_by'] ?? 0,
            'company_id' => $params['company_id']
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

        if (request()->hasFile('worker_attachment')){
            foreach($request->file('worker_attachment') as $file){
                $fileName = $file->getClientOriginalName();
                $filePath = '/workerAttachment/'.$worker['id'].'/attachment/' . $fileName; 
                $linode = $this->storage::disk('linode');
                $linode->put($filePath, file_get_contents($file));
                $fileUrl = $this->storage::disk('linode')->url($filePath);
                $this->workerAttachments::create([
                        "file_id" => $worker['id'],
                        "file_name" => $fileName,
                        "file_type" => 'WORKERATTACHMENT',
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
            "hospitalization_policy_number_valid_until" =>  ((isset($request['hospitalization_policy_number_valid_until']) && !empty($request['hospitalization_policy_number_valid_until'])) ? $request['hospitalization_policy_number_valid_until'] : null),
            "insurance_expiry_date" => ((isset($request['insurance_expiry_date']) && !empty($request['insurance_expiry_date'])) ? $request['insurance_expiry_date'] : null)
        ]);

            $workerBankDetails = $this->workerBankDetails::create([
                "worker_id" => $worker['id'],
                "bank_name" => $request['bank_name'] ?? '',
                "account_number" => $request['account_number'] ?? '',
                "socso_number" =>  $request['socso_number'] ?? ''
            ]);        

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

        $worker = $this->workers->with('directrecruitmentWorkers', 'workerAttachments', 'workerKin', 'workerVisa', 'workerBioMedical', 'workerFomema', 'workerInsuranceDetails', 'workerBankDetails')->findOrFail($request['id']);

        $worker->crm_prospect_id = $request['crm_prospect_id'] ?? $worker->crm_prospect_id;
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

        # Worker Kin details
        if( isset($worker->workerKin) && !empty($worker->workerKin) ){
            $worker->workerKin->kin_name = $request['kin_name'] ?? $worker->workerKin->kin_name;
            $worker->workerKin->kin_relationship_id = $request['kin_relationship_id'] ?? $worker->workerKin->kin_relationship_id;
            $worker->workerKin->kin_contact_number = $request['kin_contact_number'] ?? $worker->workerKin->kin_contact_number;

            $worker->workerKin->save();
        } else {
            $this->workerKin::create([
                "worker_id" => $worker->id,
                "kin_name" => $request['kin_name'] ?? '',
                "kin_relationship_id" => $request['kin_relationship_id'] ?? '',
                "kin_contact_number" =>  $request['kin_contact_number'] ?? ''         
            ]);
        }

        # Worker Visa details
        if( isset($worker->workerVisa) && !empty($worker->workerVisa) ){
            $worker->workerVisa->ksm_reference_number = $request['ksm_reference_number'] ?? $worker->workerVisa->ksm_reference_number;
            $worker->workerVisa->calling_visa_reference_number = $request['calling_visa_reference_number'] ?? $worker->workerVisa->calling_visa_reference_number;
            $worker->workerVisa->calling_visa_valid_until = $request['calling_visa_valid_until'] ?? $worker->workerVisa->calling_visa_valid_until;
            $worker->workerVisa->entry_visa_valid_until = $request['entry_visa_valid_until'] ?? $worker->workerVisa->entry_visa_valid_until;
            $worker->workerVisa->work_permit_valid_until = $request['work_permit_valid_until'] ?? $worker->workerVisa->work_permit_valid_until;

            $worker->workerVisa->save();
        } else { 
            $workerVisa = $this->workerVisa::create([
                "worker_id" => $worker->id,
                "ksm_reference_number" => $request['ksm_reference_number'],
                "calling_visa_reference_number" => $request['calling_visa_reference_number'] ?? '',
                "calling_visa_valid_until" =>  ((isset($request['calling_visa_valid_until']) && !empty($request['calling_visa_valid_until'])) ? $request['calling_visa_valid_until'] : null),         
                "entry_visa_valid_until" =>  ((isset($request['entry_visa_valid_until']) && !empty($request['entry_visa_valid_until'])) ? $request['entry_visa_valid_until'] : null),
                "work_permit_valid_until" =>  ((isset($request['work_permit_valid_until']) && !empty($request['work_permit_valid_until'])) ? $request['work_permit_valid_until'] : null)
            ]);
        }

        # Worker Bio Medical details
        if( isset($worker->workerBioMedical) && !empty($worker->workerBioMedical) ){
            $worker->workerBioMedical->bio_medical_reference_number = $request['bio_medical_reference_number'] ?? $worker->workerBioMedical->bio_medical_reference_number;
            $worker->workerBioMedical->bio_medical_valid_until = $request['bio_medical_valid_until'] ?? $worker->workerBioMedical->bio_medical_valid_until;

            $worker->workerBioMedical->save();
        } else {
            $workerBioMedical = $this->workerBioMedical::create([
                "worker_id" => $worker->id,
                "bio_medical_reference_number" => $request['bio_medical_reference_number'],
                "bio_medical_valid_until" => $request['bio_medical_valid_until'],
            ]);
        }

        # Worker Fomema details
        if( isset($worker->workerFomema) && !empty($worker->workerFomema) ){
            $worker->workerFomema->purchase_date = $request['purchase_date'] ?? $worker->workerFomema->purchase_date;
            $worker->workerFomema->clinic_name = $request['clinic_name'] ?? $worker->workerFomema->clinic_name;
            $worker->workerFomema->doctor_code = $request['doctor_code'] ?? $worker->workerFomema->doctor_code;
            $worker->workerFomema->allocated_xray = $request['allocated_xray'] ?? $worker->workerFomema->allocated_xray;
            $worker->workerFomema->xray_code = $request['xray_code'] ?? $worker->workerFomema->xray_code;

            $worker->workerFomema->save();
        } else {
            $workerFomema = $this->workerFomema::create([
                "worker_id" => $worker->id,
                "purchase_date" => ((isset($request['purchase_date']) && !empty($request['purchase_date'])) ? $request['purchase_date'] : null),
                "clinic_name" => $request['clinic_name'] ?? '',
                "doctor_code" =>  $request['doctor_code'] ?? '',         
                "allocated_xray" =>  $request['allocated_xray'] ?? '',
                "xray_code" =>  $request['xray_code'] ?? ''
            ]);
        }

        # Worker Insurance details
        if( isset($worker->workerInsuranceDetails) && !empty($worker->workerInsuranceDetails) ){
            $worker->workerInsuranceDetails->ig_policy_number = $request['ig_policy_number'] ?? $worker->workerInsuranceDetails->ig_policy_number;
            $worker->workerInsuranceDetails->ig_policy_number_valid_until = $request['ig_policy_number_valid_until'] ?? $worker->workerInsuranceDetails->ig_policy_number_valid_until;
            $worker->workerInsuranceDetails->hospitalization_policy_number = $request['hospitalization_policy_number'] ?? $worker->workerInsuranceDetails->hospitalization_policy_number;
            $worker->workerInsuranceDetails->hospitalization_policy_number_valid_until = $request['hospitalization_policy_number_valid_until'] ?? $worker->workerInsuranceDetails->hospitalization_policy_number_valid_until;
            $worker->workerInsuranceDetails->insurance_expiry_date = (isset($request['insurance_expiry_date']) && !empty($request['insurance_expiry_date'])) ? $request['insurance_expiry_date'] : $worker->workerInsuranceDetails->insurance_expiry_date;

            $worker->workerInsuranceDetails->save();
        } else {
            $workerInsuranceDetails = $this->workerInsuranceDetails::create([
                "worker_id" => $worker['id'],
                "ig_policy_number" => $request['ig_policy_number'] ?? '',
                "ig_policy_number_valid_until" => ((isset($request['ig_policy_number_valid_until']) && !empty($request['ig_policy_number_valid_until'])) ? $request['ig_policy_number_valid_until'] : null),
                "hospitalization_policy_number" =>  $request['hospitalization_policy_number'] ?? '',         
                "hospitalization_policy_number_valid_until" =>  ((isset($request['hospitalization_policy_number_valid_until']) && !empty($request['hospitalization_policy_number_valid_until'])) ? $request['hospitalization_policy_number_valid_until'] : null),
                "insurance_expiry_date" => ((isset($request['insurance_expiry_date']) && !empty($request['insurance_expiry_date'])) ? $request['insurance_expiry_date'] : null)
            ]);            
        }

        # Worker Bank details
        if( isset($worker->workerBankDetails) && !empty($worker->workerBankDetails) ){
            $worker->workerBankDetails->bank_name = $request['bank_name'] ?? $worker->workerBankDetails->bank_name;
            $worker->workerBankDetails->account_number = $request['account_number'] ?? $worker->workerBankDetails->account_number;
            $worker->workerBankDetails->socso_number = $request['socso_number'] ?? $worker->workerBankDetails->socso_number;

            $worker->workerBankDetails->save();
        } else {
            $workerBankDetails = $this->workerBankDetails::create([
                "worker_id" => $worker['id'],
                "bank_name" => $request['bank_name'] ?? '',
                "account_number" => $request['account_number'] ?? '',
                "socso_number" =>  $request['socso_number'] ?? ''
            ]);
        }
        
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

        if (request()->hasFile('worker_attachment')){

            foreach($request->file('worker_attachment') as $file){
                $fileName = $file->getClientOriginalName();
                $filePath = '/workerAttachment/'.$worker['id'].'/attachment/' . $fileName; 
                $linode = $this->storage::disk('linode');
                $linode->put($filePath, file_get_contents($file));
                $fileUrl = $this->storage::disk('linode')->url($filePath);
                $this->workerAttachments::create([
                        "file_id" => $request['id'],
                        "file_name" => $fileName,
                        "file_type" => 'WORKERATTACHMENT',
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
        return $this->workers
        ->select('workers.id', 'workers.onboarding_country_id','workers.agent_id','workers.application_id','workers.name','workers.gender', 'workers.date_of_birth', 'workers.passport_number', 'workers.passport_valid_until', 'workers.fomema_valid_until','workers.address', 'workers.status', 'workers.cancel_status', 'workers.remarks','workers.city','workers.state', 'workers.special_pass', 'workers.special_pass_submission_date', 'workers.special_pass_valid_until', 'workers.plks_status', 'workers.plks_expiry_date', 'workers.directrecruitment_status', 'workers.created_by','workers.modified_by', 'workers.crm_prospect_id', 'workers.total_management_status', 'workers.econtract_status', 'workers.module_type')
        ->with(['directrecruitmentWorkers', 'workerAttachments', 'workerKin', 'workerVisa', 'workerBioMedical', 'workerFomema', 'workerInsuranceDetails', 'workerBankDetails', 'workerFomemaAttachments', 'workerEmployment' => function ($query) {
            $query->leftJoin('total_management_project', 'total_management_project.id', '=', 'worker_employment.project_id')
            ->leftJoin('e-contract_project as econtract_project', 'econtract_project.id', '=', 'worker_employment.project_id')
            ->leftJoin('workers', 'workers.id', 'worker_employment.worker_id')
            ->leftJoin('total_management_applications', 'total_management_applications.id', 'total_management_project.application_id')
            ->leftJoin('e-contract_applications as econtrat_applications', 'econtrat_applications.id', 'econtract_project.application_id')
            ->leftjoin('directrecruitment_workers', 'workers.id', '=', 'directrecruitment_workers.worker_id')
            ->leftjoin('directrecruitment_applications', 'directrecruitment_applications.id', '=', 'directrecruitment_workers.application_id')
            ->leftJoin('crm_prospects as crm_prospects_tm', 'crm_prospects_tm.id', 'total_management_applications.crm_prospect_id')
            ->leftJoin('crm_prospects as crm_prospects_econt', 'crm_prospects_econt.id', 'econtrat_applications.crm_prospect_id')
            ->leftJoin('crm_prospects as crm_prospects_dr', 'crm_prospects_dr.id', 'directrecruitment_applications.crm_prospect_id')
            ->leftJoin('crm_prospect_services as crm_prospect_services_tm', 'crm_prospect_services_tm.id', 'total_management_applications.service_id')
            ->leftJoin('crm_prospect_services as crm_prospect_services_econt', 'crm_prospect_services_econt.id', 'econtrat_applications.service_id')
            ->leftJoin('crm_prospect_services as crm_prospect_services_dr', 'crm_prospect_services_dr.id', 'directrecruitment_applications.service_id')
            ->select('worker_employment.project_id', 'worker_employment.worker_id', 'worker_employment.work_start_date', 'worker_employment.work_end_date', 'worker_employment.remove_date', 'worker_employment.service_type')
            ->selectRaw("(CASE WHEN (worker_employment.service_type = 'Total Management') THEN crm_prospects_tm.company_name 
        WHEN (BINARY worker_employment.service_type = 'e-Contract') THEN crm_prospects_econt.company_name 
        WHEN (directrecruitment_workers.worker_id IS NOT NULL) THEN crm_prospects_dr.company_name 
        ELSE '".Config::get('services.FOMNEXTS_DETAILS')['company_name']."' END) as assignment_company_name, (CASE WHEN (worker_employment.service_type = 'Total Management') THEN crm_prospects_tm.roc_number 
        WHEN (BINARY worker_employment.service_type = 'e-Contract') THEN crm_prospects_econt.roc_number 
        WHEN (directrecruitment_workers.worker_id IS NOT NULL) THEN crm_prospects_dr.roc_number 
        ELSE '".Config::get('services.FOMNEXTS_DETAILS')['roc_number']."' END) as assigned_roc_number, (CASE WHEN (worker_employment.service_type = 'Total Management') THEN total_management_project.name 
        WHEN (BINARY worker_employment.service_type = 'e-Contract') THEN econtract_project.name 
        WHEN (directrecruitment_workers.worker_id IS NOT NULL) THEN crm_prospects_dr.address 
        ELSE '".Config::get('services.FOMNEXTS_DETAILS')['location']."' END) as assignment_project, (CASE WHEN (worker_employment.service_type = 'Total Management') THEN total_management_project.city 
        WHEN (BINARY worker_employment.service_type = 'e-Contract') THEN econtract_project.city 
        WHEN (directrecruitment_workers.worker_id IS NOT NULL) THEN crm_prospects_dr.address 
        ELSE '".Config::get('services.FOMNEXTS_DETAILS')['location']."' END) as assignment_city, (CASE WHEN (worker_employment.service_type = 'Total Management') THEN total_management_project.state 
        WHEN (BINARY worker_employment.service_type = 'e-Contract') THEN econtract_project.state 
        WHEN (directrecruitment_workers.worker_id IS NOT NULL) THEN crm_prospects_dr.address 
        ELSE '".Config::get('services.FOMNEXTS_DETAILS')['location']."' END) as assignment_state, (CASE WHEN (worker_employment.service_type = 'Total Management') THEN crm_prospect_services_tm.sector_name 
        WHEN (BINARY worker_employment.service_type = 'e-Contract') THEN crm_prospect_services_econt.sector_name 
        WHEN (directrecruitment_workers.worker_id IS NOT NULL) THEN crm_prospect_services_dr.sector_name 
        ELSE '".Config::get('services.FOMNEXTS_DETAILS')['location']."' END) as assignment_sector")
        ->distinct('worker_employment.worker_id', 'worker_employment.project_id');
        }])->findOrFail($request['id']);
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
        $user = JWTAuth::parseToken()->authenticate();
        $request['company_id'] = $this->authServices->getCompanyIds($user);

        $data = $this->workers->join('worker_visa', 'workers.id', '=', 'worker_visa.worker_id')
        ->leftjoin('worker_bio_medical', 'workers.id', '=', 'worker_bio_medical.worker_id')
        ->leftJoin('crm_prospects', 'crm_prospects.id', '=', 'workers.crm_prospect_id')
        ->leftJoin('worker_employment', function ($join) {
            $join->on('workers.id', '=', 'worker_employment.worker_id')
                 ->where('worker_employment.transfer_flag', 0)
                 ->whereNull('worker_employment.remove_date');
        })
        ->leftJoin('total_management_project', 'total_management_project.id', '=', 'worker_employment.project_id')
        ->leftJoin('e-contract_project as econtract_project', 'econtract_project.id', '=', 'worker_employment.project_id')
        ->leftjoin('directrecruitment_workers', 'workers.id', '=', 'directrecruitment_workers.worker_id')
        ->where(function ($query) use ($request) {
            if((isset($request['crm_prospect_id']) && !empty($request['crm_prospect_id'])) || (isset($request['crm_prospect_id']) && $request['crm_prospect_id'] == 0)) {
                $query->where('workers.crm_prospect_id', $request['crm_prospect_id']);
            }
            if (isset($request['search_param']) && !empty($request['search_param'])) {
                $query->where('workers.name', 'like', "%{$request['search_param']}%")
                ->orWhere('workers.passport_number', 'like', '%'.$request['search_param'].'%')
                ->orWhere('worker_visa.ksm_reference_number', 'like', '%'.$request['search_param'].'%');
            }
            
        })
        ->whereIn('workers.company_id', $request['company_id'])
        ->where(function ($query) use ($user) {
            if ($user['user_type'] == 'Customer') {
                $query->where('workers.crm_prospect_id', '=', $user['reference_id']);
            }
        })
        ->select('workers.id','workers.name', 'workers.passport_number', 'workers.module_type', 'worker_employment.service_type', 'worker_employment.id as worker_employment_id', 'worker_employment.project_id')
        ->selectRaw("(CASE WHEN (workers.crm_prospect_id = 0) THEN '".Config::get('services.FOMNEXTS_DETAILS')['company_name']."' ELSE crm_prospects.company_name END) as company_name,  
		(CASE WHEN (worker_employment.service_type = 'Total Management') THEN total_management_project.city 
        WHEN (worker_employment.service_type = 'e-Contract') THEN econtract_project.city 
        ELSE null END) as project_location,
		(CASE WHEN (worker_employment.service_type = 'Total Management') THEN workers.total_management_status
		WHEN (worker_employment.service_type = 'e-Contract') THEN workers.econtract_status
		ELSE 'On-Bench' END) as status");
        if(isset($request['status']) && !empty($request['status'])) {
            $data = $data->whereRaw("(CASE WHEN (worker_employment.service_type = 'Total Management') THEN workers.total_management_status
		WHEN (worker_employment.service_type = 'e-Contract') THEN workers.econtract_status
		ELSE 'On-Bench' END) = '".$request['status']."'");
        }
        $data = $data->distinct('workers.id')
        ->orderBy('workers.id','DESC')
        ->paginate(Config::get('services.paginate_row'));
        return $data;
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

        $user = JWTAuth::parseToken()->authenticate();
        $request['company_id'] = $this->authServices->getCompanyIds($user);

        return $this->workers->join('worker_visa', 'workers.id', '=', 'worker_visa.worker_id')
        ->join('worker_kin', 'workers.id', '=', 'worker_kin.worker_id')
        ->join('kin_relationship', 'kin_relationship.id', '=', 'worker_kin.kin_relationship_id')
        ->join('worker_bio_medical', 'workers.id', '=', 'worker_bio_medical.worker_id')
        ->leftjoin('worker_arrival', 'workers.id', '=', 'worker_arrival.worker_id')
        ->leftjoin('directrecruitment_workers', 'workers.id', '=', 'directrecruitment_workers.worker_id')
        ->where('directrecruitment_workers.application_id', $request['application_id'])
        ->where('directrecruitment_workers.onboarding_country_id', $request['onboarding_country_id'])
        ->whereIn('workers.company_id', $request['company_id'])
        //->where('directrecruitment_workers.agent_id', $request['agent_id'])
        ->where(function ($query) use ($user) {
            if ($user['user_type'] == 'Customer') {
                $query->where('workers.crm_prospect_id', '=', $user['reference_id']);
            }
        })
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
        $user = JWTAuth::parseToken()->authenticate();
        $request['company_id'] = $this->authServices->getCompanyIds($user);

        return $this->workers->join('worker_visa', 'workers.id', '=', 'worker_visa.worker_id')
        ->leftjoin('directrecruitment_workers', 'workers.id', '=', 'directrecruitment_workers.worker_id')
        ->where('workers.status', 1)
        ->where('directrecruitment_workers.application_id', $request['application_id'])
        ->where('directrecruitment_workers.onboarding_country_id', $request['onboarding_country_id'])
        ->where('directrecruitment_workers.agent_id', $request['agent_id'])
        ->where('worker_visa.status', 'Pending')
        ->whereIn('workers.company_id', $request['company_id'])
        ->where(function ($query) use ($user) {
            if ($user['user_type'] == 'Customer') {
                $query->where('workers.crm_prospect_id', '=', $user['reference_id']);
            }
        })
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
        ->select('directrecruitment_onboarding_agent.id as id','agent.agent_name')
        ->distinct('directrecruitment_onboarding_agent.id','agent.agent_name')
        ->orderBy('directrecruitment_onboarding_agent.id','ASC')->get();
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
    /**
     * @param $request
     * @return array|bool
     */
    public function assignWorker($request): array|bool
    {
        $params = $request->all();
        $user = JWTAuth::parseToken()->authenticate();
        $params['created_by'] = $user['id'];

        $validator = Validator::make($request->toArray(), $this->assignWorkerValidation());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }

        if(isset($request['workers']) && !empty($request['workers'])) {
            foreach ($request['workers'] as $workerId) {
                $directrecruitmentWorkers = $this->directrecruitmentWorkers->updateOrCreate([
                    "worker_id" => $workerId,
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
            }
            return true;
        }else {
            return false;
        }
        
    }

    /**
     * @param $request
     * @return mixed
     */
    public function createBankDetails($request) : mixed
    {

        $workerBankDetail = $this->workerBankDetails::where('worker_id', $request['worker_id'])->count();
        if(isset($workerBankDetail) && $workerBankDetail > 3){
            return [
                'workerCountError' => true 
            ];
        }

        $params = $request->all();
        $user = JWTAuth::parseToken()->authenticate();
        $params['created_by'] = $user['id'];

        //$workerBankDetail = $this->workerBankDetails::findOrFail($request['id']);
        
        $workerBankDetail = $this->workerBankDetails->create([
            'worker_id' => $request['worker_id'],
            "bank_name" => $request['bank_name'] ?? '',
            "account_number" => $request['account_number'] ?? '',
            "socso_number" =>  $request['socso_number'] ?? ''
        ]);

        return $workerBankDetail;
    }

    /**
     * @param $request
     * @return bool|array
     */
    public function updateBankDetails($request): bool|array
    {

        $workerBankDetail = $this->workerBankDetails::where('worker_id', $request['worker_id'])->count();
        
        if(isset($workerBankDetail) && $workerBankDetail > 3){
            return [
                'workerCountError' => true 
            ];
        }

        $params = $request->all();
        $user = JWTAuth::parseToken()->authenticate();
        $params['modified_by'] = $user['id'];

        $workerBankDetail = $this->workerBankDetails::findOrFail($request['id']);
        $workerBankDetail->worker_id = $request['worker_id'] ?? $workerBankDetail->worker_id;
        $workerBankDetail->bank_name = $request['bank_name'] ?? $workerBankDetail->bank_name;
        $workerBankDetail->account_number = $request['account_number'] ?? $workerBankDetail->account_number;
        $workerBankDetail->socso_number = $request['socso_number'] ?? $workerBankDetail->socso_number;

        $workerBankDetail->save();

        return true;
    }

    /**
     * @param $request
     * @return mixed
     */
    public function showBankDetails($request) : mixed
    {
        if(!($this->validationServices->validate($request,['id' => 'required']))){
            return [
                'validate' => $this->validationServices->errors()
            ];
        }
        return $this->workerBankDetails->findOrFail($request['id']);
    }

    /**
     * @param $request
     * @return mixed
     */
    public function listBankDetails($request) : mixed
    {
        if(isset($request['search_param']) && !empty($request['search_param'])){
            if(!($this->validationServices->validate($request,['search_param' => 'required|min:3']))){
                return [
                    'validate' => $this->validationServices->errors()
                ];
            }
        }
        return $this->workerBankDetails
        
        ->where('worker_bank_details.worker_id', $request['worker_id'])
        ->where(function ($query) use ($request) {
            if (isset($request['search_param']) && !empty($request['search_param'])) {
                $query->where('worker_bank_details.bank_name', 'like', "%{$request['search_param']}%")
                ->orWhere('worker_bank_details.account_number', 'like', '%'.$request['search_param'].'%');
            }
            
        })->select('worker_bank_details.id','worker_bank_details.worker_id','worker_bank_details.bank_name','worker_bank_details.account_number','worker_bank_details.socso_number')
        ->distinct()
        ->orderBy('worker_bank_details.created_at','DESC')
        ->paginate(Config::get('services.paginate_row'));
    }

    /**
     * delete the specified Bank Detail of the Worker.
     *
     * @param $request
     * @return mixed
     */    
    public function deleteBankDetails($request): mixed
    {   
        $workerBankDetail = $this->workerBankDetails::find($request['id']);

        if(is_null($workerBankDetail)){
            return [
                "isDeleted" => false,
                "message" => "Data not found"
            ];
        }
        $workerBankDetail->delete();
        return [
            "isDeleted" => true,
            "message" => "Deleted Successfully"
        ];
    }
    /**
     * add attachment
     * @param $request
     * @return bool|array
     */
    public function addAttachment($request): bool|array
    {
        $params = $request->all();
        $user = JWTAuth::parseToken()->authenticate();
        $params['created_by'] = $user['id'];

        $validator = Validator::make($request->toArray(), $this->addAttachmentValidation());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }
        $workerExists = $this->workers->find($request['worker_id']);
        if(is_null($workerExists)) {
            return ['workerError' => true];
        }
        if (request()->hasFile('attachment')){
            foreach($request->file('attachment') as $file){
                $fileName = $file->getClientOriginalName();
                $filePath = '/workerAttachment/'.$request['worker_id'].'/attachment/' . $fileName; 
                $linode = $this->storage::disk('linode');
                $linode->put($filePath, file_get_contents($file));
                $fileUrl = $this->storage::disk('linode')->url($filePath);
                $this->workerAttachments::create([
                        "file_id" => $request['worker_id'],
                        "file_name" => $fileName,
                        "file_type" => 'WORKERATTACHMENT',
                        "file_url" =>  $fileUrl         
                    ]);  
            }
            return true;
        }else{
            return false;
        }
    }
    /**
     * @param $request
     * @return mixed
     */
    public function listAttachment($request) : mixed
    {
        return $this->workers
        ->select('workers.id')
        ->where('workers.id', $request['worker_id'])
        ->with(['workerOtherAttachments' => function ($query) { 
            $query->select(['id', 'file_id', 'file_name', 'file_type', 'file_url', DB::raw('1 as edit_flag')]);
        }])
        ->with('SpecialPassAttachments', 'WorkerRepatriationAttachments', 'WorkerPLKSAttachments', 'workerFomemaAttachments', 'CancellationAttachment', 'WorkerImmigrationAttachments', 'WorkerInsuranceAttachments')
        ->paginate(Config::get('services.paginate_row'));
    }
    /**
     *
     * @param $request
     * @return bool
     */    
    public function deleteAttachment($request): bool
    {   
        $data = $this->workerAttachments->find($request['attachment_id']);
        if(is_null($data)) {
            return false;
        }
        $data->delete();
        return true;
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
        $params['company_id'] = $user['company_id'];

        $workerBulkUpload = $this->workerBulkUpload->create([
                'name' => 'Worker Bulk Upload',
                'type' => 'Worker bulk upload',
                'module_type' => 'Workers'
            ]
        );

        Excel::import(new CommonWorkerImport($params, $workerBulkUpload), $file);
        return true;
    }
}
