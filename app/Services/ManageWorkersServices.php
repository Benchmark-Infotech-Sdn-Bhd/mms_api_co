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
use App\Services\DirectRecruitmentOnboardingCountryServices;
use App\Services\ValidationServices;
use Illuminate\Support\Facades\Config;
use App\Services\AuthServices;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\WorkerImport;
use App\Exports\WorkerImportParentSheetExport;

class ManageWorkersServices
{
    private Workers $workers;
    private WorkerAttachments $workerAttachments;
    private WorkerKin $workerKin;
    private WorkerVisa $workerVisa;
    Private WorkerVisaAttachments $workerVisaAttachments;
    Private WorkerBioMedical $workerBioMedical;
    Private WorkerBioMedicalAttachments $workerBioMedicalAttachments;
    Private WorkerFomema $workerFomema;
    Private WorkerInsuranceDetails $workerInsuranceDetails;
    Private WorkerBankDetails $workerBankDetails;
    Private KinRelationship $kinRelationship;
    Private DirectRecruitmentCallingVisaStatus $directRecruitmentCallingVisaStatus;
    Private DirectRecruitmentOnboardingAgent $directRecruitmentOnboardingAgent;
    private WorkerStatus $workerStatus;
    private WorkerBulkUpload $workerBulkUpload;
    private DirectRecruitmentOnboardingCountryServices $directRecruitmentOnboardingCountryServices;
    private ValidationServices $validationServices;
    private AuthServices $authServices;
    private Storage $storage;
    /**
     * ManageWorkersServices constructor.
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
     * @param WorkerBulkUpload $workerBulkUpload
     * @param DirectRecruitmentOnboardingCountryServices $directRecruitmentOnboardingCountryServices;
     * @param ValidationServices $validationServices
     * @param AuthServices $authServices
     * @param Storage $storage
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
            WorkerBulkUpload                            $workerBulkUpload,
            DirectRecruitmentOnboardingCountryServices  $directRecruitmentOnboardingCountryServices, 
            ValidationServices                          $validationServices,
            AuthServices                                $authServices,
            Storage                                     $storage
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
        $this->workerBulkUpload = $workerBulkUpload;
        $this->validationServices = $validationServices;
        $this->directRecruitmentOnboardingCountryServices = $directRecruitmentOnboardingCountryServices;
        $this->authServices = $authServices;
        $this->storage = $storage;
        $this->directRecruitmentCallingVisaStatus = $directRecruitmentCallingVisaStatus;
        $this->directRecruitmentOnboardingAgent = $directRecruitmentOnboardingAgent;
    }
    /**
     * @return array
     */
    public function createValidation(): array
    {
        return
            [
                'name' => 'required|regex:/^[a-zA-Z ]*$/|max:255',
                'date_of_birth' => 'required|date_format:Y-m-d',
                'gender' => 'required|regex:/^[a-zA-Z]*$/|max:15',
                'passport_number' => 'required|regex:/^[a-zA-Z0-9]*$/',
                'passport_valid_until' => 'required|date_format:Y-m-d',
                'address' => 'required',
                'city' => 'regex:/^[a-zA-Z ]*$/|max:150',
                'state' => 'required|regex:/^[a-zA-Z ]*$/|max:150'
            ];
    }
    /**
     * @return array
     */
    public function updateValidation($id): array
    {
        return
            [
                'id' => 'required|regex:/^[0-9]+$/',
                'name' => 'required|regex:/^[a-zA-Z ]*$/|max:255',
                'date_of_birth' => 'required|date_format:Y-m-d',
                'gender' => 'required|regex:/^[a-zA-Z]*$/|max:15',
                'passport_number' => 'required|regex:/^[a-zA-Z0-9]*$/',
                'passport_valid_until' => 'required|date_format:Y-m-d',
                'address' => 'required',
                'city' => 'regex:/^[a-zA-Z ]*$/|max:150',
                'state' => 'required|regex:/^[a-zA-Z ]*$/|max:150'
            ];
    }

    /**
     * @return array
     */
    public function bulkUploadValidation(): array
    {
        return [
            'onboarding_country_id' => 'required',
            'application_id' => 'required',
            'agent_id' => 'required',
            'worker_file' => 'required|mimes:xlsx,xls'
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
        if(!($this->validationServices->validate($request->toArray(),$this->createValidation()))){
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

        if(!($this->validationServices->validate($request->toArray(),$this->updateValidation($request['id'])))){
            return [
                'validate' => $this->validationServices->errors()
            ];
        }

        $worker = $this->workers->with('workerAttachments', 'workerKin', 'workerVisa', 'workerBioMedical', 'workerFomema', 'workerInsuranceDetails', 'workerBankDetails')->findOrFail($request['id']);

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
        return $this->workers->with('workerAttachments', 'workerKin', 'workerVisa', 'workerBioMedical', 'workerFomema', 'workerInsuranceDetails', 'workerBankDetails')->findOrFail($request['id']);
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
        ->leftjoin('worker_bio_medical', 'workers.id', '=', 'worker_bio_medical.worker_id')
        ->leftjoin('worker_arrival', 'workers.id', '=', 'worker_arrival.worker_id')
        ->leftJoin('crm_prospects', 'crm_prospects.id', '=', 'workers.crm_prospect_id')
        ->where(function ($query) use ($request) {
            if(isset($request['crm_prospect_id']) && !empty($request['crm_prospect_id'])) {
                $query->where('workers.crm_prospect_id', $request['crm_prospect_id']);
            }
            if(isset($request['status']) && !empty($request['status'])) {
                $query->where('workers.total_management_status', $request['status']);
            }
            if (isset($request['search_param']) && !empty($request['search_param'])) {
                $query->where('workers.name', 'like', "%{$request['search_param']}%")
                ->orWhere('workers.passport_number', 'like', '%'.$request['search_param'].'%')
                ->orWhere('worker_visa.ksm_reference_number', 'like', '%'.$request['search_param'].'%');
            }
            
        })->select('workers.id','workers.name', 'workers.passport_number', 'workers.address', 'workers.city', 'workers.state', 'workers.crm_prospect_id', 'crm_prospects.company_name', 'workers.total_management_status')
        ->distinct()
        ->orderBy('workers.id','DESC')
        ->paginate(Config::get('services.paginate_row'));
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
    public function exportTemplate($request): mixed
    {
        $params = $request->all();
        $user = JWTAuth::parseToken()->authenticate();
            
        $fileName = "importWorker".$params['application_id'].".xlsx";
        $filePath = '/upload/worker/' . $fileName; 
        Excel::store(new WorkerImportParentSheetExport($params, []), $filePath, 'linode');
        $fileUrl = $this->storage::disk('linode')->url($filePath);            
        return $fileUrl;
    }

}
