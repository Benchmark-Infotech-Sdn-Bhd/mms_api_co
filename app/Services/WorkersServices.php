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
use App\Models\BulkUploadRecords;
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
use App\Exports\FailureExport;
use App\Exports\WorkerBiodataFailureExport;

class WorkersServices
{
    public const DEFAULT_VALUE = 0;
    public const ERROR_UNAUTHORIZED = ['unauthorizedError' => true];
    public const ERROR_QUEUE = ['queueError' => true];
    public const ERROR_WORKER = ['workerError' => true];
    public const ERROR_WORKER_COUNT = ['workerCountError' => true];

    public const ATTACHMENT_FILE_TYPE_FOMEMA = 'FOMEMA';
    public const ATTACHMENT_FILE_TYPE_PASSPORT = 'PASSPORT';
    public const ATTACHMENT_FILE_TYPE_PROFILE = 'PROFILE';
    public const ATTACHMENT_FILE_TYPE_WORKERATTACHMENT = 'WORKERATTACHMENT';
    public const ATTACHMENT_FILE_TYPE_WORKPERMIT = 'WORKPERMIT';
    public const ATTACHMENT_FILE_TYPE_BIOMEDICAL = 'BIOMEDICAL';
    public const MODULE_TYPE_WORKERBIODATA = 'WorkerBioData';
    public const MODULE_TYPE_WORKERS = 'Workers';
    public const PROCESS_STATUS = 'Processed';
    public const WORKER_BULK_UPLOAD = 'Worker Bulk Upload';
    public const MESSAGE_DATA_NOT_FOUND = 'Data not found';
    public const MESSAGE_DELETED_SUCCESSFULLY = 'Deleted Successfully';
    public const MESSAGE_UPDATED_SUCCESSFULLY = 'Updated Successfully';
    public const WORKER_BANK_ACCOUNT_LIMIT = 3;
    public const USER_TYPE_CUSTOMER = 'Customer';

    /**
     * @var Workers
     */
    private Workers $workers;
    /**
     * @var WorkerAttachments
     */
    private WorkerAttachments $workerAttachments;
    /**
     * @var WorkerKin
     */
    private WorkerKin $workerKin;
    /**
     * @var WorkerVisa
     */
    private WorkerVisa $workerVisa;
    /**
     * @var WorkerVisaAttachments
     */
    private WorkerVisaAttachments $workerVisaAttachments;
    /**
     * @var WorkerBioMedical
     */
    private WorkerBioMedical $workerBioMedical;
    /**
     * @var WorkerBioMedicalAttachments
     */
    private WorkerBioMedicalAttachments $workerBioMedicalAttachments;
    /**
     * @var WorkerFomema
     */
    private WorkerFomema $workerFomema;
    /**
     * @var WorkerInsuranceDetails
     */
    private WorkerInsuranceDetails $workerInsuranceDetails;
    /**
     * @var WorkerBankDetails
     */
    private WorkerBankDetails $workerBankDetails;
    /**
     * @var KinRelationship
     */
    private KinRelationship $kinRelationship;
    /**
     * @var DirectRecruitmentCallingVisaStatus
     */
    private DirectRecruitmentCallingVisaStatus $directRecruitmentCallingVisaStatus;
    /**
     * @var DirectRecruitmentOnboardingAgent
     */
    private DirectRecruitmentOnboardingAgent $directRecruitmentOnboardingAgent;
    /**
     * @var WorkerStatus
     */
    private WorkerStatus $workerStatus;
    /**
     * @var DirectRecruitmentOnboardingCountryServices
     */
    private DirectRecruitmentOnboardingCountryServices $directRecruitmentOnboardingCountryServices;
    /**
     * @var ValidationServices
     */
    private ValidationServices $validationServices;
    /**
     * @var AuthServices
     */
    private AuthServices $authServices;
    /**
     * @var Storage
     */
    private Storage $storage;
    /**
     * @var DirectrecruitmentWorkers
     */
    private DirectrecruitmentWorkers $directrecruitmentWorkers;
    /**
     * @var WorkerBulkUpload
     */
    private WorkerBulkUpload $workerBulkUpload;
    /**
     * @var BulkUploadRecords
     */
    private BulkUploadRecords $bulkUploadRecords;

    /**
     * WorkersServices constructor method
     *
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
     * @param BulkUploadRecords $bulkUploadRecords
     *
     * @return void
     *
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
            WorkerBulkUpload                            $workerBulkUpload,
            BulkUploadRecords                           $bulkUploadRecords
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
        $this->bulkUploadRecords = $bulkUploadRecords;
    }
    /**
     * validate the assign worker request data
     *
     * @return array The validation rules for the input data.
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
     * validate the add attachment request data
     *
     * @return array The validation rules for the input data.
     */
    public function addAttachmentValidation(): array
    {
        return [
            'worker_id' => 'required',
        ];
    }

    /**
     * Get the Authenticated User data
     *
     * @return mixed Returns the enriched request data.
     *
     */
    private function getAuthenticatedUser(): mixed
    {
        return JWTAuth::parseToken()->authenticate();
    }

    /**
     * Enriches the given request data with user details.
     *
     * @param array $request The request data to be enriched.
     * @return mixed Returns the enriched request data.
     */
    private function enrichRequestWithUserDetails($request): mixed
    {
        $user = JWTAuth::parseToken()->authenticate();
        $request['created_by'] = $user['id'];
        $request['modified_by'] = $user['id'];
        $request['company_id'] = $this->authServices->getCompanyIds($user);

        return $request;
    }

    /**
     * Validate the create worker request data.
     *
     * @param array $request The request data to be validated.
     * @return array|bool Returns an array with 'error' as key and validation error messages as value if validation fails.
     *                   Returns true if validation passes.
     */
    private function validateCreateRequest($request): array|bool
    {
        if(!($this->validationServices->validate($request->toArray(),$this->workers->rules))){
            return [
              'validate' => $this->validationServices->errors()
            ];
        }

        return true;
    }

    /**
     * Validate the update worker request data.
     *
     * @param array $request The request data to be validated.
     * @return array|bool Returns an array with 'error' as key and validation error messages as value if validation fails.
     *                   Returns true if validation passes.
     */
    private function validateUpdateRequest($request): array|bool
    {
        if(!($this->validationServices->validate($request->toArray(),$this->workers->rulesForUpdation($request['id'])))){
            return [
                'validate' => $this->validationServices->errors()
            ];
        }

        return true;
    }

    /**
     * Validate the worker show request data.
     *
     * @param array $request The request data to be validated.
     * @return array|bool Returns an array with 'error' as key and validation error messages as value if validation fails.
     *                   Returns true if validation passes.
     */
    private function validateShowRequest($request): array|bool
    {
        if(!($this->validationServices->validate($request,['id' => 'required']))){
            return [
                'validate' => $this->validationServices->errors()
            ];
        }

        return true;
    }

    /**
     * Validate the worker list request data.
     *
     * @param array $request The request data to be validated.
     * @return array|bool Returns an array with 'error' as key and validation error messages as value if validation fails.
     *                   Returns true if validation passes.
     */
    private function validateListRequest($request): array|bool
    {
        if(!empty($request['search_param'])){
            if(!($this->validationServices->validate($request,['search_param' => 'required|min:3']))){
                return [
                    'validate' => $this->validationServices->errors()
                ];
            }
        }

        return true;
    }

    /**
     * Validate the worker bank list request data.
     *
     * @param array $request The request data to be validated.
     * @return array|bool Returns an array with 'error' as key and validation error messages as value if validation fails.
     *                   Returns true if validation passes.
     */
    private function validateListBankDetailsRequest($request): array|bool
    {
        if(!empty($request['search_param'])){
            if(!($this->validationServices->validate($request,['search_param' => 'required|min:3']))){
                return [
                    'validate' => $this->validationServices->errors()
                ];
            }
        }

        return true;
    }

    /**
     * Validate the worker bank show details request data.
     *
     * @param array $request The request data to be validated.
     * @return array|bool Returns an array with 'error' as key and validation error messages as value if validation fails.
     *                   Returns true if validation passes.
     */
    private function validateShowBankDetailsRequest($request): array|bool
    {
        if(!($this->validationServices->validate($request,['id' => 'required']))){
            return [
                'validate' => $this->validationServices->errors()
            ];
        }

        return true;
    }

    /**
     * Validate the worker add attachment request data.
     *
     * @param array $request The request data to be validated.
     * @return array|bool Returns an array with 'error' as key and validation error messages as value if validation fails.
     *                   Returns true if validation passes.
     */
    private function validateAddAttachmentRequest($request): array|bool
    {
        $validator = Validator::make($request->toArray(), $this->addAttachmentValidation());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }

        return true;
    }

    /**
     * Validate the assign worker request data.
     *
     * @param array $request The request data to be validated.
     * @return array|bool Returns an array with 'error' as key and validation error messages as value if validation fails.
     *                   Returns true if validation passes.
     */
    private function validateAssignWorkerRequest($request): array|bool
    {
        $validator = Validator::make($request->toArray(), $this->assignWorkerValidation());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }

        return true;
    }

    /**
     * Validate the worker export request data.
     *
     * @param array $request The request data to be validated.
     * @return array|bool Returns an array with 'error' as key and validation error messages as value if validation fails.
     *                   Returns true if validation passes.
     */
    private function validateExportRequest($request): array|bool
    {
        if(!empty($request['search_param'])){
            if(!($this->validationServices->validate($request,['search_param' => 'required|min:3']))){
                return [
                    'validate' => $this->validationServices->errors()
                ];
            }
        }

        return true;
    }

    /**
     * create a worker
     *
     * @param $request The request data containing create worker details
     *
     * @return mixed Returns true if the worker is created successfully, otherwise returns an array with error details
     *
     * @see validateCreateRequest()
     * @see enrichRequestWithUserDetails()
     * @see createWorker()
     * @see uploadFomemaAttachment()
     * @see uploadPassportAttachment()
     * @see uploadProfilePicture()
     * @see uploadWorkerAttachment()
     * @see createWorkerKin()
     * @see createWorkerVisa()
     * @see uploadWorkerVisaAttachment()
     * @see createWorkerBioMedical()
     * @see uploadWorkerBioMedicalAttachment()
     * @see createWorkerFomema()
     * @see createWorkerInsuranceDetails()
     * @see createWorkerBankDetails()
     *
     */
    public function create($request) : mixed
    {
        $validationResult = $this->validateCreateRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }

        $params = $request->all();
        $user = $this->getAuthenticatedUser();
        $params['created_by'] = $user['id'];
        $params['company_id'] = $user['company_id'];

        $worker = $this->createWorker($params);

        $this->uploadFomemaAttachment($worker, $request);
        $this->uploadPassportAttachment($worker, $request);
        $this->uploadProfilePicture($worker, $request);
        $this->uploadWorkerAttachment($worker, $request);
        $this->createWorkerKin($worker, $request);
        $workerVisa = $this->createWorkerVisa($worker, $request);
        $this->uploadWorkerVisaAttachment($worker, $workerVisa, $request);
        $workerBioMedical = $this->createWorkerBioMedical($worker, $request);
        $this->uploadWorkerBioMedicalAttachment($worker, $workerBioMedical, $request);
        $this->createWorkerFomema($worker, $request);
        $this->createWorkerInsuranceDetails($worker, $request);
        $this->createWorkerBankDetails($worker, $request);

        return $worker;
    }

    /**
     * create worker.
     *
     * @param array $request
     *              crm_prospect_id (int) ID of the prospect
     *              name (string) name of the worker
     *              gender (string) gender of the worker
     *              date_of_birth (date) DOB of the worker
     *              passport_number (string) passport number the worker
     *              passport_valid_until (date) passport valid date of the worker
     *              fomema_valid_until (date) fomema valid date of the worker
     *              address (string) address of the worker
     *              city (string) city of the worker
     *              state (string) state of the worker
     *              created_by ID of the user who created the worker
     *              company_id (int) user company id
     *
     * @return mixed  Returns the created worker data.
     */
    private function createWorker($request): mixed
    {
        return $this->workers->create([
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
            'created_by'    => $request['created_by'] ?? 0,
            'modified_by'   => $request['created_by'] ?? 0,
            'company_id' => $request['company_id']
        ]);
    }

    /**
     * upload Fomema Attachment.
     *
	 * @param object $worker The worker object
     * @param array $request
     *              fomema_attachment (file) - the attachment file of fomema
     *
     * @return void
     */
    private function uploadFomemaAttachment($worker, $request): void
    {
        if (request()->hasFile('fomema_attachment')){
            foreach($request->file('fomema_attachment') as $file){
                $fileName = $file->getClientOriginalName();
                $filePath = '/workerbiodata/'.$worker['id'].'/fomema/'. $fileName;
                $linode = $this->storage::disk('linode');
                $linode->put($filePath, file_get_contents($file));
                $fileUrl = $this->storage::disk('linode')->url($filePath);
                $this->workerAttachments->updateOrCreate(
                    [
                        "file_id" => $worker['id'],
                        "file_type" => self::ATTACHMENT_FILE_TYPE_FOMEMA,
                    ],
                    [
                        "file_name" => $fileName,
                        "file_url" =>  $fileUrl
                ]);
            }
        }
    }

    /**
     * upload Passport Attachment.
     *
	 * @param object $worker The worker object
     * @param array $request
     *              passport_attachment (file) - The attachment file
     *
     * @return void
     */
    private function uploadPassportAttachment($worker, $request): void
    {
        if (request()->hasFile('passport_attachment')){
            foreach($request->file('passport_attachment') as $file){
                $fileName = $file->getClientOriginalName();
                $filePath = '/workerbiodata/'.$worker['id'].'/passport/' . $fileName;
                $linode = $this->storage::disk('linode');
                $linode->put($filePath, file_get_contents($file));
                $fileUrl = $this->storage::disk('linode')->url($filePath);
                $this->workerAttachments->updateOrCreate(
                    [
                        "file_id" => $worker['id'],
                        "file_type" => self::ATTACHMENT_FILE_TYPE_PASSPORT,
                    ],
                    [
                        "file_name" => $fileName,
                        "file_url" =>  $fileUrl
                ]);
            }
        }
    }

    /**
     * upload profile picture.
     *
	 * @param object $worker The worker object
     * @param array $request
     *              profile_picture (file) - The attachment file
     *
     * @return void
     */
    private function uploadProfilePicture($worker, $request): void
    {
        if (request()->hasFile('profile_picture')){
            foreach($request->file('profile_picture') as $file){
                $fileName = $file->getClientOriginalName();
                $filePath = '/workerbiodata/'.$worker['id'].'/profile/' . $fileName;
                $linode = $this->storage::disk('linode');
                $linode->put($filePath, file_get_contents($file));
                $fileUrl = $this->storage::disk('linode')->url($filePath);
                $this->workerAttachments->updateOrCreate(
                    [
                        "file_id" => $worker['id'],
                        "file_type" => self::ATTACHMENT_FILE_TYPE_PROFILE,
                    ],
                    [
                        "file_name" => $fileName,
                        "file_url" =>  $fileUrl
                ]);
            }
        }
    }

    /**
     * upload worker attachment.
     *
	 * @param object $worker The worker object
     * @param array $request
     *              worker_attachment (file) - The attachment file
     *
     * @return void
     */
    private function uploadWorkerAttachment($worker, $request): void
    {
        if (request()->hasFile('worker_attachment')){
            foreach($request->file('worker_attachment') as $file){
                $fileName = $file->getClientOriginalName();
                $filePath = '/workerAttachment/'.$worker['id'].'/attachment/' . $fileName;
                $linode = $this->storage::disk('linode');
                $linode->put($filePath, file_get_contents($file));
                $fileUrl = $this->storage::disk('linode')->url($filePath);
                $this->workerAttachments->updateOrCreate(
                    [
                        "file_id" => $worker['id'],
                        "file_type" => self::ATTACHMENT_FILE_TYPE_WORKERATTACHMENT,
                    ],
                    [
                        "file_name" => $fileName,
                        "file_url" =>  $fileUrl
                ]);
            }
        }
    }

    /**
     * Create the worker kin .
     *
	 * @param object $worker The worker object
     * @param array $request
     *              kin_name (string) Kin name
     *              kin_relationship_id (int) kin relationship id
     *              kin_contact_number (string) kin contact number
     *
     * @return void
     */
    private function createWorkerKin($worker, $request)
    {
        $this->workerKin::create([
            "worker_id" => $worker['id'],
            "kin_name" => $request['kin_name'] ?? '',
            "kin_relationship_id" => $request['kin_relationship_id'] ?? '',
            "kin_contact_number" =>  $request['kin_contact_number'] ?? ''
        ]);
    }

    /**
     * Create the worker visa .
     *
	 * @param object $worker The worker object
     * @param array $request
     *              ksm_reference_number (int) ksm reference number
     *              calling_visa_reference_number (int) calling visa reference number
     *              calling_visa_valid_until (date) calling visa valid until date
	 *              entry_visa_valid_until (date) entry visa valid until date
	 *				work_permit_valid_until (date) work permit valid until date
     *
     * @return mixed Returns the created worker visa data.
     */
    private function createWorkerVisa($worker, $request): mixed
    {
        return $this->workerVisa::create([
            "worker_id" => $worker['id'],
            "ksm_reference_number" => $request['ksm_reference_number'],
            "calling_visa_reference_number" => $request['calling_visa_reference_number'] ?? '',
            "calling_visa_valid_until" =>  ((isset($request['calling_visa_valid_until']) && !empty($request['calling_visa_valid_until'])) ? $request['calling_visa_valid_until'] : null),
            "entry_visa_valid_until" =>  ((isset($request['entry_visa_valid_until']) && !empty($request['entry_visa_valid_until'])) ? $request['entry_visa_valid_until'] : null),
            "work_permit_valid_until" =>  ((isset($request['work_permit_valid_until']) && !empty($request['work_permit_valid_until'])) ? $request['work_permit_valid_until'] : null)
        ]);
    }

    /**
     * Upload Worker Visa Attachment
     *
	 * @param object $worker The worker object
	 * @param object $workerVisa The worker visa object
     * @param array $request
     *              worker_visa_attachment (file) - The attachment file
     *
     * @return void
     */
    private function uploadWorkerVisaAttachment($worker, $workerVisa, $request): void
    {
        if (request()->hasFile('worker_visa_attachment')){
            foreach($request->file('worker_visa_attachment') as $file){
                $fileName = $file->getClientOriginalName();
                $filePath = '/workerbiodata/'.$worker['id'].'/workerVisa/' . $fileName;
                $linode = $this->storage::disk('linode');
                $linode->put($filePath, file_get_contents($file));
                $fileUrl = $this->storage::disk('linode')->url($filePath);
                    $this->workerVisaAttachments->updateOrCreate(
                        [
                            "file_id" => $workerVisa['id'],
                            "file_type" => self::ATTACHMENT_FILE_TYPE_WORKPERMIT,
                        ],
                        [
                            "file_name" => $fileName,
                            "file_url" =>  $fileUrl
                    ]);
            }
        }
    }

    /**
     * Create the Worker BioMedical Record.
     *
	 * @param object $worker The worker object
     * @param array $request
     *              bio_medical_reference_number (int) bio medical reference number
     *              bio_medical_valid_until (date) bio medical valid until date
     *
     * @return mixed Returns the created worker biomedical data.
     */
    private function createWorkerBioMedical($worker, $request): mixed
    {
        return $this->workerBioMedical::create([
            "worker_id" => $worker['id'],
            "bio_medical_reference_number" => $request['bio_medical_reference_number'],
            "bio_medical_valid_until" => $request['bio_medical_valid_until'],
        ]);
    }

    /**
     * upload Worker BioMedical Attachment
     *
	 * @param object $worker The worker object
	 * @param object $workerBioMedical The workerBioMedical object
     * @param array $request
     *              worker_bio_medical_attachment (file) - The attachment file
     *
     * @return void
     */
    private function uploadWorkerBioMedicalAttachment($worker, $workerBioMedical, $request): void
    {
        if (request()->hasFile('worker_bio_medical_attachment')){
            foreach($request->file('worker_bio_medical_attachment') as $file){
                $fileName = $file->getClientOriginalName();
                $filePath = '/workerbiodata/'.$worker['id'].'/workerBioMedical/' . $fileName;
                $linode = $this->storage::disk('linode');
                $linode->put($filePath, file_get_contents($file));
                $fileUrl = $this->storage::disk('linode')->url($filePath);
                    $this->workerBioMedicalAttachments->updateOrCreate(
                        [
                            "file_id" => $workerBioMedical['id'],
                            "file_type" => self::ATTACHMENT_FILE_TYPE_BIOMEDICAL,
                        ],
                        [
                            "file_name" => $fileName,
                            "file_url" =>  $fileUrl
                    ]);
            }
        }
    }

    /**
     * Create the Worker Fomema Record.
     *
	 * @param object $worker The worker object
     * @param array $request
     *              purchase_date (date) purchase date
     *              clinic_name (string) clinic name
	 *              doctor_code (string) doctor_code
	 *			    allocated_xray (string) allocated xray
	 *			    xray_code (string) xray code

     *
     * @return void
     */
    private function createWorkerFomema($worker, $request)
    {
        $this->workerFomema::create([
            "worker_id" => $worker['id'],
            "purchase_date" => ((isset($request['purchase_date']) && !empty($request['purchase_date'])) ? $request['purchase_date'] : null),
            "clinic_name" => $request['clinic_name'] ?? '',
            "doctor_code" =>  $request['doctor_code'] ?? '',
            "allocated_xray" =>  $request['allocated_xray'] ?? '',
            "xray_code" =>  $request['xray_code'] ?? ''
        ]);
    }

    /**
     * Create the Worker InsuranceDetails Record.
     *
	 * @param object $worker The worker object
     * @param array $request
     *              ig_policy_number (int) ig policy number
     *              ig_policy_number_valid_until (date) ig policy number valid until date
	 *              hospitalization_policy_number (int) hospitalization policy number
	 *			    hospitalization_policy_number_valid_until (date) hospitalization policy number valid until date
	 *			    insurance_expiry_date (date) insurance expiry date

     *
     * @return void
     */
    private function createWorkerInsuranceDetails($worker, $request)
    {
        $this->workerInsuranceDetails::create([
            "worker_id" => $worker['id'],
            "ig_policy_number" => $request['ig_policy_number'] ?? '',
            "ig_policy_number_valid_until" => ((isset($request['ig_policy_number_valid_until']) && !empty($request['ig_policy_number_valid_until'])) ? $request['ig_policy_number_valid_until'] : null),
            "hospitalization_policy_number" =>  $request['hospitalization_policy_number'] ?? '',
            "hospitalization_policy_number_valid_until" =>  ((isset($request['hospitalization_policy_number_valid_until']) && !empty($request['hospitalization_policy_number_valid_until'])) ? $request['hospitalization_policy_number_valid_until'] : null),
            "insurance_expiry_date" => ((isset($request['insurance_expiry_date']) && !empty($request['insurance_expiry_date'])) ? $request['insurance_expiry_date'] : null)
        ]);
    }

    /**
     * Create the Worker BankDetails Record.
     *
	 * @param object $worker The worker object
     * @param array $request
     *              bank_name (string) bank name
     *              account_number (int) account number
	 *              socso_number (int) socso number
	 *
     *
     * @return void
     */
    private function createWorkerBankDetails($worker, $request)
    {
        $this->workerBankDetails::create([
                "worker_id" => $worker['id'],
                "bank_name" => $request['bank_name'] ?? '',
                "account_number" => $request['account_number'] ?? '',
                "socso_number" =>  $request['socso_number'] ?? ''
            ]);
    }

    /**
     * Get the worker based on the given request data.
     *
     * @param array $request The request data containing the company ID and id.
     * @return mixed Returns the worker matching the given company ID and id,
     *               or null if no matching worker is found.
     */
    private function getWorker($request)
    {
        return $this->workers::whereIn('company_id', $request['company_id'])->find($request['id']);
    }

    /**
     * Get the worker based on the given request data.
     *
     * @param array $request The request data containing the worker id.
     * @return mixed Returns the worker matching the given worker id,
     *               or null if no matching worker is found.
     */
    private function getWorkerForUpdate($request)
    {
        return $this->workers->with('directrecruitmentWorkers', 'workerAttachments', 'workerKin', 'workerVisa', 'workerBioMedical', 'workerFomema', 'workerInsuranceDetails', 'workerBankDetails')->findOrFail($request['id']);
    }

    /**
     * Update the worker detail
     *
     * @param $request  The request data containing update worker details
     *
     * @return bool|array Returns true if the worker is updated successfully, otherwise returns an array with error details
     *                    Returns ERROR_UNAUTHORIZED if the worker company is not mapped with current user company
     */
    public function update($request): bool|array
    {

        $validationResult = $this->validateUpdateRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }

        $params = $request->all();
        $params = $this->enrichRequestWithUserDetails($params);

        $workerData = $this->getWorker($params);
        if(is_null($workerData)){
            return self::ERROR_UNAUTHORIZED;
        }

        $worker = $this->getWorkerForUpdate($params);

        $this->updateWorkerData($worker,$params);

        # Worker Kin details
        $this->updateWorkerKin($worker,$params);

        # Worker Visa details
        $this->updateWorkerVisa($worker, $request);

        # Worker Bio Medical details
        $this->updateWorkerBioMedical($worker, $request);

        # Worker Fomema details
        $this->updateWorkerFomema($worker, $request);

        # Worker Insurance details
        $this->updateWorkerInsuranceDetails($worker, $request);

        # Worker Bank details
        $this->updateWorkerBankDetails($worker, $request);

        $worker->save();

        $this->uploadFomemaAttachment($worker, $request);
        $this->uploadPassportAttachment($worker, $request);
        $this->uploadProfilePicture($worker, $request);
        $this->uploadWorkerAttachment($worker, $request);
        $workerVisa = $worker->workerVisa;
        $this->uploadWorkerVisaAttachment($worker, $workerVisa, $request);
        $workerBioMedical = $worker->workerBioMedical;
        $this->uploadWorkerBioMedicalAttachment($worker, $workerBioMedical, $request);

        return true;
    }

    /**
     * update the Worker data.
     *
	 * @param object $worker The worker object
     * @param array $request
     *              crm_prospect_id (int) ID of the prospect
     *              name (string) name of the worker
     *              gender (string) gender of the worker
     *              date_of_birth (date) DOB of the worker
     *              passport_number (string) passport number the worker
     *              passport_valid_until (date) passport valid date of the worker
     *              fomema_valid_until (date) fomema valid date of the worker
     *              address (string) address of the worker
     *              city (string) city of the worker
     *              state (string) state of the worker
     *              created_by (int) ID of the user who created the worker
	 *              modified_by (int) ID of the user who modified the worker
     *              company_id (int) user company id
	 *
     *
     * @return void
     */
    private function updateWorkerData($worker, $request)
    {
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
        $worker->modified_by = $request['modified_by'];
    }

    /**
     * update the Worker kin.
     *
	 * @param object $worker The worker object
     * @param array $request the request data cotaining the below params
     *              kin_name (string) name of the kin
     *              kin_relationship_id (int) ID of the kin
     *              kin_contact_number (int) contact number of the kin
	 *
     *
     * @return void
     */
    private function updateWorkerKin($worker, $request)
    {
        if ( isset($worker->workerKin) && !empty($worker->workerKin) ){
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
    }

    /**
     * update the Worker Visa.
     *
	 * @param object $worker The worker object
     * @param array $request the request data cotaining the below params
     *              ksm_reference_number (string) KSM reference number of the worker
     *              calling_visa_reference_number (string) calling visa reference number of the worker
     *              calling_visa_valid_until (date) calling visa valid until date of the worker
     *              entry_visa_valid_until (date) entry visa valid unitil date of the worker
     *              work_permit_valid_until (date) work permit valid until date of the worker
	 *
     *
     * @return void
     */
    private function updateWorkerVisa($worker, $request)
    {
        if( isset($worker->workerVisa) && !empty($worker->workerVisa) ){
            $worker->workerVisa->ksm_reference_number = $request['ksm_reference_number'] ?? $worker->workerVisa->ksm_reference_number;
            $worker->workerVisa->calling_visa_reference_number = $request['calling_visa_reference_number'] ?? $worker->workerVisa->calling_visa_reference_number;
            $worker->workerVisa->calling_visa_valid_until = $request['calling_visa_valid_until'] ?? $worker->workerVisa->calling_visa_valid_until;
            $worker->workerVisa->entry_visa_valid_until = $request['entry_visa_valid_until'] ?? $worker->workerVisa->entry_visa_valid_until;
            $worker->workerVisa->work_permit_valid_until = $request['work_permit_valid_until'] ?? $worker->workerVisa->work_permit_valid_until;

            $worker->workerVisa->save();
        } else {
            $this->workerVisa::create([
                "worker_id" => $worker->id,
                "ksm_reference_number" => $request['ksm_reference_number'],
                "calling_visa_reference_number" => $request['calling_visa_reference_number'] ?? '',
                "calling_visa_valid_until" =>  ((isset($request['calling_visa_valid_until']) && !empty($request['calling_visa_valid_until'])) ? $request['calling_visa_valid_until'] : null),
                "entry_visa_valid_until" =>  ((isset($request['entry_visa_valid_until']) && !empty($request['entry_visa_valid_until'])) ? $request['entry_visa_valid_until'] : null),
                "work_permit_valid_until" =>  ((isset($request['work_permit_valid_until']) && !empty($request['work_permit_valid_until'])) ? $request['work_permit_valid_until'] : null)
            ]);
        }
    }

    /**
     * update the Worker BioMedical.
     *
	 * @param object $worker The worker object
     * @param array $request request data containing the below params
	 *              bio_medical_reference_number (int) bio medical reference number of the worker
     *              bio_medical_valid_until (date) bio medical valid until date of the worker
     * 
     * @return void
     */
    private function updateWorkerBioMedical($worker, $request)
    {
        if( isset($worker->workerBioMedical) && !empty($worker->workerBioMedical) ){
            $worker->workerBioMedical->bio_medical_reference_number = $request['bio_medical_reference_number'] ?? $worker->workerBioMedical->bio_medical_reference_number;
            $worker->workerBioMedical->bio_medical_valid_until = $request['bio_medical_valid_until'] ?? $worker->workerBioMedical->bio_medical_valid_until;

            $worker->workerBioMedical->save();
        } else {
            $this->workerBioMedical::create([
                "worker_id" => $worker->id,
                "bio_medical_reference_number" => $request['bio_medical_reference_number'],
                "bio_medical_valid_until" => $request['bio_medical_valid_until'],
            ]);
        }
    }

    /**
     * update the Worker Fomema.
     *
	 * @param object $worker The worker object
     * @param array $request request data containing the below params
     *              purchase_date (date) date of the purchase
	 *              clinic_name (string) clinic name
     *              doctor_code (string) doctor code
     *              allocated_xray (string) allocated xray
     *              xray_code (string) xray code
     *
     * @return void
     */
    private function updateWorkerFomema($worker, $request)
    {
        if( isset($worker->workerFomema) && !empty($worker->workerFomema) ){
            $worker->workerFomema->purchase_date = $request['purchase_date'] ?? $worker->workerFomema->purchase_date;
            $worker->workerFomema->clinic_name = $request['clinic_name'] ?? $worker->workerFomema->clinic_name;
            $worker->workerFomema->doctor_code = $request['doctor_code'] ?? $worker->workerFomema->doctor_code;
            $worker->workerFomema->allocated_xray = $request['allocated_xray'] ?? $worker->workerFomema->allocated_xray;
            $worker->workerFomema->xray_code = $request['xray_code'] ?? $worker->workerFomema->xray_code;

            $worker->workerFomema->save();
        } else {
            $this->workerFomema::create([
                "worker_id" => $worker->id,
                "purchase_date" => ((isset($request['purchase_date']) && !empty($request['purchase_date'])) ? $request['purchase_date'] : null),
                "clinic_name" => $request['clinic_name'] ?? '',
                "doctor_code" =>  $request['doctor_code'] ?? '',
                "allocated_xray" =>  $request['allocated_xray'] ?? '',
                "xray_code" =>  $request['xray_code'] ?? ''
            ]);
        }
    }

    /**
     * update the Worker InsuranceDetails.
     *
	 * @param object $worker The worker object
     * @param array $request request data containing the below params
     *              ig_policy_number (int) ig policy number of the worker
	 *              ig_policy_number_valid_until (date) ig policy number valid until date
     *              hospitalization_policy_number (int) hospitalization policy number of the worker
     *              hospitalization_policy_number_valid_until (date) hospitalization policy number valid until date
     *              insurance_expiry_date (date) insurance expiry date
     *
     * @return void
     */
    private function updateWorkerInsuranceDetails($worker, $request)
    {
        if( isset($worker->workerInsuranceDetails) && !empty($worker->workerInsuranceDetails) ){
            $worker->workerInsuranceDetails->ig_policy_number = $request['ig_policy_number'] ?? $worker->workerInsuranceDetails->ig_policy_number;
            $worker->workerInsuranceDetails->ig_policy_number_valid_until = $request['ig_policy_number_valid_until'] ?? $worker->workerInsuranceDetails->ig_policy_number_valid_until;
            $worker->workerInsuranceDetails->hospitalization_policy_number = $request['hospitalization_policy_number'] ?? $worker->workerInsuranceDetails->hospitalization_policy_number;
            $worker->workerInsuranceDetails->hospitalization_policy_number_valid_until = $request['hospitalization_policy_number_valid_until'] ?? $worker->workerInsuranceDetails->hospitalization_policy_number_valid_until;
            $worker->workerInsuranceDetails->insurance_expiry_date = (isset($request['insurance_expiry_date']) && !empty($request['insurance_expiry_date'])) ? $request['insurance_expiry_date'] : $worker->workerInsuranceDetails->insurance_expiry_date;

            $worker->workerInsuranceDetails->save();
        } else {
            $this->workerInsuranceDetails::create([
                "worker_id" => $worker['id'],
                "ig_policy_number" => $request['ig_policy_number'] ?? '',
                "ig_policy_number_valid_until" => ((isset($request['ig_policy_number_valid_until']) && !empty($request['ig_policy_number_valid_until'])) ? $request['ig_policy_number_valid_until'] : null),
                "hospitalization_policy_number" =>  $request['hospitalization_policy_number'] ?? '',
                "hospitalization_policy_number_valid_until" =>  ((isset($request['hospitalization_policy_number_valid_until']) && !empty($request['hospitalization_policy_number_valid_until'])) ? $request['hospitalization_policy_number_valid_until'] : null),
                "insurance_expiry_date" => ((isset($request['insurance_expiry_date']) && !empty($request['insurance_expiry_date'])) ? $request['insurance_expiry_date'] : null)
            ]);
        }
    }

    /**
     * update the Worker BankDetails.
     *
	 * @param object $worker The worker object
     * @param array $request request data containing the below params
	 *              bank_name (string) name of the bank
     *              account_number (int) bank account number
     *              socso_number (int) socso number
     *
     * @return void
     */
    private function updateWorkerBankDetails($worker, $request)
    {
        if( isset($worker->workerBankDetails) && !empty($worker->workerBankDetails) ){
            $worker->workerBankDetails->bank_name = $request['bank_name'] ?? $worker->workerBankDetails->bank_name;
            $worker->workerBankDetails->account_number = $request['account_number'] ?? $worker->workerBankDetails->account_number;
            $worker->workerBankDetails->socso_number = $request['socso_number'] ?? $worker->workerBankDetails->socso_number;

            $worker->workerBankDetails->save();
        } else {
            $this->workerBankDetails::create([
                "worker_id" => $worker['id'],
                "bank_name" => $request['bank_name'] ?? '',
                "account_number" => $request['account_number'] ?? '',
                "socso_number" =>  $request['socso_number'] ?? ''
            ]);
        }
    }

    /**
     * Show the work detail
     *
     * @param $request
     *        id (int) ID of the worker
     *
     * @return mixed Returns the worker detail
     */
    public function show($request) : mixed
    {
        $validationResult = $this->validateShowRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }

        $request = $this->enrichRequestWithUserDetails($request);

        return $this->workers
        ->select('workers.id', 'workers.onboarding_country_id','workers.agent_id','workers.application_id','workers.name','workers.gender', 'workers.date_of_birth', 'workers.passport_number', 'workers.passport_valid_until', 'workers.fomema_valid_until','workers.address', 'workers.status', 'workers.cancel_status', 'workers.remarks','workers.city','workers.state', 'workers.special_pass', 'workers.special_pass_submission_date', 'workers.special_pass_valid_until', 'workers.plks_status', 'workers.plks_expiry_date', 'workers.directrecruitment_status', 'workers.created_by','workers.modified_by', 'workers.crm_prospect_id', 'workers.total_management_status', 'workers.econtract_status', 'workers.module_type')
        ->with(['directrecruitmentWorkers', 'workerAttachments', 'workerKin', 'workerVisa', 'workerBioMedical', 'workerFomema', 'workerInsuranceDetails', 'workerBankDetails', 'workerFomemaAttachments', 'workerEmployment' => function ($query) {
            $this->showSelectColumns($query);
        }])
        ->whereIn('workers.company_id', $request['company_id'])
        ->find($request['id']);
    }

    /**
     * Added the query
     *
     * @param object $query
     *
     * @return mixed Returns the query builder object
     */
    private function showSelectColumns($query)
    {
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
    }

    /**
     * List the workers
     *
     * @param $request
     *        crm_prospect_id (int) ID of the prospect
     *        search_param (string) search parameter
     *        status (string) worker status filter
     *
     * @return mixed Returns The paginated list of workers
     */
    public function list($request) : mixed
    {
        $validationResult = $this->validateListRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
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
        ->leftjoin('directrecruitment_workers', 'workers.id', '=', 'directrecruitment_workers.worker_id');
        $data = $this->listApplyCondition($request,$data);
        $data = $this->listApplySearchFilter($request,$data);
        // $data = $this->listApplyReferenceFilter($user,$data);
        $data = $this->listSelectColumns($data);
        $status = $request['status'] ?? '';
        if(!empty($status)) {
            $data = $data->whereRaw("(CASE WHEN (worker_employment.service_type = 'Total Management') THEN workers.total_management_status
		WHEN (worker_employment.service_type = 'e-Contract') THEN workers.econtract_status
		ELSE 'On-Bench' END) = '".$status."'");
        }
        $data = $data->distinct('workers.id')
        ->orderBy('workers.id','DESC')
        ->paginate(Config::get('services.paginate_worker_row'));
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
        return $data->whereIn('workers.company_id', $request['company_id']);
    }

    /**
     * Apply search filter to the query builder based on user data
     *
     * @param array $request The user data
     *        search (string) search parameter
     *
     * @return mixed $data Returns the query builder object with the applied search filter
     */
    private function listApplySearchFilter($request,$data)
    {
        return $data->where(function ($query) use ($request) {
            // if((isset($request['crm_prospect_id']) && !empty($request['crm_prospect_id'])) || (isset($request['crm_prospect_id']) && $request['crm_prospect_id'] == 0)) {
            //     $query->where('workers.crm_prospect_id', $request['crm_prospect_id']);
            // }
            
            if((isset($request['ksm_reference_number']) && !empty($request['ksm_reference_number'])) || (isset($request['ksm_reference_number']) && $request['ksm_reference_number'] == 0)) {
                    $query->where('worker_visa.ksm_reference_number', $request['ksm_reference_number']);
            }    
        
            if (isset($request['search_param']) && !empty($request['search_param'])) {
                $query->where('workers.name', 'like', '%'.$request['search_param'].'%')
                ->orWhere('workers.passport_number', 'like', '%'.$request['search_param'].'%')
                ->orWhere('worker_visa.ksm_reference_number', 'like', '%'.$request['search_param'].'%');
            }

        });
    }

    /**
     * Apply reference filter to the query builder based on user data
     *
     * @param object  $user The user object
     *
     * @return mixed $data Returns the query builder object with the applied search filter
     */
    private function listApplyReferenceFilter($user,$data)
    {
        return $data->where(function ($query) use ($user) {
            if ($user['user_type'] == self::USER_TYPE_CUSTOMER) {
                $query->where('workers.crm_prospect_id', '=', $user['reference_id']);
            }
        });
    }


    /**
     * Select data from the query.
     *
     * @return mixed $data The modified instance of the class.
     */
    private function listSelectColumns($data)
    {
        return $data->select('workers.id','workers.name', 'workers.passport_number', 'workers.module_type', 'worker_employment.service_type', 'worker_employment.id as worker_employment_id', 'worker_employment.project_id','worker_visa.ksm_reference_number')
        ->selectRaw("(CASE WHEN (workers.crm_prospect_id = 0) THEN '".Config::get('services.FOMNEXTS_DETAILS')['company_name']."' ELSE crm_prospects.company_name END) as company_name,
		(CASE WHEN (worker_employment.service_type = 'Total Management') THEN total_management_project.city
        WHEN (worker_employment.service_type = 'e-Contract') THEN econtract_project.city
        ELSE null END) as project_location,
		(CASE WHEN (worker_employment.service_type = 'Total Management') THEN workers.total_management_status
		WHEN (worker_employment.service_type = 'e-Contract') THEN workers.econtract_status
		ELSE 'On-Bench' END) as status");
    }

    /**
     * Export the workers
     *
     * @param $request
     *        crm_prospect_id (int) ID of the prospect
     *        search_param (string) search parameter
     *        status (string) worker status filter
     *
     * @return mixed Returns the workers
     */
    public function export($request) : mixed
    {
        $validationResult = $this->validateExportRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }

        $user = JWTAuth::parseToken()->authenticate();
        $request['company_id'] = $this->authServices->getCompanyIds($user);

        return $this->workers->join('worker_visa', 'workers.id', '=', 'worker_visa.worker_id')
        ->join('worker_kin', 'workers.id', '=', 'worker_kin.worker_id')
        ->join('kin_relationship', 'kin_relationship.id', '=', 'worker_kin.kin_relationship_id')
        ->join('worker_bio_medical', 'workers.id', '=', 'worker_bio_medical.worker_id')
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
            $this->exportSearchFilter($query, $request);

        })
        ->whereIn('workers.company_id', $request['company_id'])
        ->where(function ($query) use ($user) {
            $this->exportCustomerFilter($query, $user);
        })
         ->select('workers.id','workers.name','workers.date_of_birth','workers.gender','workers.passport_number','workers.passport_valid_until','workers.address','workers.state','worker_kin.kin_name','kin_relationship.name as kin_relationship_name','worker_kin.kin_contact_number','worker_visa.ksm_reference_number','worker_bio_medical.bio_medical_reference_number','worker_bio_medical.bio_medical_valid_until')
         ->distinct('workers.id')
         ->orderBy('workers.id','DESC')->get();
    }

    /**
     * Apply the search filter to the query
     *
     * @param $query $query The query builder instance
     * @param array $request The request data containing the  crm_prospect_id, search_param
     *
     * @return void
     */
    private function exportSearchFilter($query, $request)
    {
        if((isset($request['crm_prospect_id']) && !empty($request['crm_prospect_id'])) || (isset($request['crm_prospect_id']) && $request['crm_prospect_id'] == 0)) {
                $query->where('workers.crm_prospect_id', $request['crm_prospect_id']);
        }
        $search = $request['search_param'] ?? '';
        if (!empty($search)) {
            $query->where('workers.name', 'like', "%{$search}%")
            ->orWhere('workers.passport_number', 'like', '%'.$search.'%')
            ->orWhere('worker_visa.ksm_reference_number', 'like', '%'.$search.'%');
        }
    }

    /**
     * Apply the customer filter to the query
     *
     * @param object $query The query builder instance
     * @param object $user
     *
     * @return void
     */
    private function exportCustomerFilter($query, $user)
    {
        if ($user['user_type'] == self::USER_TYPE_CUSTOMER) {
            $query->where('workers.crm_prospect_id', '=', $user['reference_id']);
        }
    }

    /**
     * workers dropdown
     *
     * @param $request
     *        application_id (int) ID of the application
     *        onboarding_country_id (int) ID of the onboarding country
     *        agent_id (int) ID of the agent
     *
     * @return mixed Returns the workers
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
        ->where('worker_visa.ksm_reference_number', $request['ksm_reference_number'])
        ->whereIn('workers.company_id', $request['company_id'])
        // ->where(function ($query) use ($user) {
        //     if ($user['user_type'] == self::USER_TYPE_CUSTOMER) {
        //         $query->where('workers.crm_prospect_id', '=', $user['reference_id']);
        //     }
        // })
        ->select('workers.id','workers.name')
        ->orderBy('workers.created_at','DESC')->get();
    }
    /**
     * Update the worker status
     *
     * @param $request
     *        id (int) ID of the worker
     *        status (int) ID of the worker status
     *
     * @return array Returns an array with two keys: 'isUpdated' and 'message'
     */
    public function updateStatus($request) : array
    {
        $user = JWTAuth::parseToken()->authenticate();
        $request['company_id'] = $user['company_id'];
        $worker = $this->workers
        ->where('id', $request['id'])
        ->where('company_id',$request['company_id'])
        ->update(['status' => $request['status']]);
        return  [
            "isUpdated" => $worker,
            "message" => self::MESSAGE_UPDATED_SUCCESSFULLY
        ];
    }

    /**
     * List the kinRelationship
     *
     * @return mixed Returns the Kin relationship data
     */
    public function kinRelationship() : mixed
    {
        return $this->kinRelationship->where('status', 1)
        ->select('id','name')
        ->orderBy('id','ASC')->get();
    }

    /**
     * List the onboarding agent
     *
     * @param $request
     *        application_id (int) ID of the application
     *        onboarding_country_id (int) ID of the onboarding country
     *
     * @return mixed Returns an onboarding agent
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
     * Submit the Replace worker
     *
     * @param $request The request data containing replace worker details
     *
     * @return array Returns an array with two keys: 'isUpdated' and 'message'
     */
    public function replaceWorker($request) : array
    {
        $user = JWTAuth::parseToken()->authenticate();
        $request['company_id'] = $user['company_id'];

        $worker = $this->workers
        ->where('id', $request['id'])
        ->where('company_id',$request['company_id'])
        ->update([
            'replace_worker_id' => $request['replace_worker_id'],
            'replace_by' => $user['id'],
            'replace_at' => Carbon::now()->format('Y-m-d H:i:s')
        ]);
        return  [
            "isUpdated" => $worker,
            "message" => self::MESSAGE_UPDATED_SUCCESSFULLY
        ];
    }

    /**
     * List the worker status
     *
     * @param $request
     *        application_id (int) ID of the application
     *        onboarding_country_id (int) ID of the onboarding country
     *
     * @return mixed Returns the worker status
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
     * Submit the Assign Workers
     *
     * @param $request The request data containing assign worker details
     *
     * @return array|bool Returns true if the assign worker is submitted successfully, otherwise returns an array with error details
     */
    public function assignWorker($request): array|bool
    {
        $validationResult = $this->validateAssignWorkerRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }

        $request = $request->all();
        $request = $this->enrichRequestWithUserDetails($request);

        if(isset($request['workers']) && !empty($request['workers'])) {
            $this->processAssignWorkers($request);
            return true;
        }else {
            return false;
        }

    }

    /**
     * Process the assign workers.
     *
     * @param array $request $request The request data containing the 'workers', 'onboarding_country_id', 'agent_id', 'application_id',  'created_by'
     *
     * @return void
     *
     */
    private function processAssignWorkers($request)
    {
        foreach ($request['workers'] as $workerId) {

            $this->processDirectrecruitmentWorkers($workerId, $request);

            $this->processDirectRecruitmentCallingVisaStatus($request);

            $this->processWorkerStatus($request);

            $onBoardingStatus['application_id'] = $request['application_id'];
            $onBoardingStatus['country_id'] = $request['onboarding_country_id'];
            $onBoardingStatus['onboarding_status'] = 4; //Agent Added
            $this->directRecruitmentOnboardingCountryServices->onboarding_status_update($onBoardingStatus);
        }
    }

    /**
     * Process Directrecruitment Workers
     *
	 * @param int $worworkerId The id of the worker
     * @param array $request containing the 'onboarding_country_id', 'agent_id', 'application_id', 'created_by'
     *
     * @return void
     */
    private function processDirectrecruitmentWorkers($workerId, $request)
    {
        $this->directrecruitmentWorkers->updateOrCreate([
            "worker_id" => $workerId,
            'onboarding_country_id' => $request['onboarding_country_id'] ?? 0,
            'agent_id' => $request['agent_id'] ?? 0,
            'application_id' => $request['application_id'] ?? 0,
            'created_by'    => $request['created_by'] ?? 0,
            'modified_by'   => $request['created_by'] ?? 0
        ]);
    }

    /**
     * Process Direct Recruitment Calling Visa Status
     *
     * @param array $request containing the 'onboarding_country_id', 'agent_id', 'application_id', 'created_by'
     *
     * @return void
     */
    private function processDirectRecruitmentCallingVisaStatus($request)
    {
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
                    'created_by' => $request['created_by'] ?? 0,
                    'modified_by' => $request['created_by'] ?? 0,
                ]);
            }
    }

    /**
     * Process Worker Status
     *
     * @param array $request containing the 'onboarding_country_id', 'agent_id', 'application_id', 'created_by'
     *
     * @return void
     */
    private function processWorkerStatus($request)
    {
        $checkWorkerStatus = $this->workerStatus
            ->where('application_id', $request['application_id'])
            ->where('onboarding_country_id', $request['onboarding_country_id'])
            ->get()->toArray();

            if(isset($checkWorkerStatus) && count($checkWorkerStatus) > 0 ){
                $this->workerStatus->where([
                    'application_id' => $request['application_id'],
                    'onboarding_country_id' => $request['onboarding_country_id']
                ])->update(['updated_on' => Carbon::now(), 'modified_by' => $request['created_by']]);
            } else {
                $workerStatus = $this->workerStatus->create([
                    'application_id' => $request['application_id'] ?? 0,
                    'onboarding_country_id' => $request['onboarding_country_id'] ?? 0,
                    'item' => 'Worker Biodata',
                    'updated_on' => Carbon::now(),
                    'status' => 1,
                    'created_by' => $request['created_by'] ?? 0,
                    'modified_by' => $request['created_by'] ?? 0,
                ]);
            }
    }

    /**
     * Create a Bank detail for worker
     *
     * @param array $request request data containing the below params
     *              worker_id (int) ID of the worker
     *              bank_name (string) worker bank name
     *              account_number (int) bank account number
     *              socso_number (int) socso number
     *
     * @return mixed Returns the created bank detail record. otherwise returns an array with error details
     *               Returns ERROR_UNAUTHORIZED  - if worker company id is not mapped with user company id
     *               Returns ERROR_WORKER_COUNT - if the no.of bank account exceed the limit
     */
    public function createBankDetails($request) : mixed
    {

        $params = $request->all();
        $user = $this->getAuthenticatedUser();
        $params['created_by'] = $user['id'];
        $params['company_id'] = $user['company_id'];

        $workerData = $this->getWorkerByCompanyId($params);
        if(is_null($workerData)){
            return self::ERROR_UNAUTHORIZED;
        }

        $workerBankDetail = $this->getWorkerBankDetailsCount($request);
        if(isset($workerBankDetail) && $workerBankDetail > self::WORKER_BANK_ACCOUNT_LIMIT){
            return self::ERROR_WORKER_COUNT;
        }

        $workerBankDetail = $this->workerBankDetails->create([
            'worker_id' => $request['worker_id'],
            "bank_name" => $request['bank_name'] ?? '',
            "account_number" => $request['account_number'] ?? '',
            "socso_number" =>  $request['socso_number'] ?? ''
        ]);

        return $workerBankDetail;
    }

    /**
     * Update the bank detail for worker
     *
     * @param array $request
     *              id (int) ID of the update bank account record
     *              worker_id (int) ID of the worker
     *              bank_name (string) worker bank name
     *              account_number (int) bank account number
     *              socso_number (int) socso number
     *
     * @return bool|array Returns true if the bank detail is updated successfully, otherwise returns an array with error details
     *                    Returns ERROR_UNAUTHORIZED  - if worker company id is not mapped with user company id
     *                    Returns ERROR_WORKER_COUNT - if the no.of bank account exceed the limit
     */
    public function updateBankDetails($request): bool|array
    {
        $params = $request->all();
        $user = $this->getAuthenticatedUser();
        $params['modified_by'] = $user['id'];
        $params['company_id'] = $user['company_id'];

        $workerData = $this->getWorkerByCompanyId($params);
        if(is_null($workerData)){
            return self::ERROR_UNAUTHORIZED;
        }

        $workerBankDetail = $this->getWorkerBankDetailsCount($request);

        if(isset($workerBankDetail) && $workerBankDetail > self::WORKER_BANK_ACCOUNT_LIMIT){
            return self::ERROR_WORKER_COUNT;
        }

        $workerBankDetail = $this->workerBankDetails::findOrFail($request['id']);
        $workerBankDetail->worker_id = $request['worker_id'] ?? $workerBankDetail->worker_id;
        $workerBankDetail->bank_name = $request['bank_name'] ?? $workerBankDetail->bank_name;
        $workerBankDetail->account_number = $request['account_number'] ?? $workerBankDetail->account_number;
        $workerBankDetail->socso_number = $request['socso_number'] ?? $workerBankDetail->socso_number;

        $workerBankDetail->save();

        return true;
    }

    /**
     * Shows the bank detail
     *
     * @param $request
     *        id (int) ID of the bank record
     *
     * @return mixed Returns the Bank detail
     */
    public function showBankDetails($request) : mixed
    {
        $validationResult = $this->validateShowBankDetailsRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }
        $request = $this->enrichRequestWithUserDetails($request);

        return $this->workerBankDetails->join('workers', function ($join) use ($request) {
            $join->on('workers.id', '=', 'worker_bank_details.worker_id')
                 ->whereIn('workers.company_id', $request['company_id']);
        })->select('worker_bank_details.id', 'worker_bank_details.worker_id', 'worker_bank_details.bank_name','worker_bank_details.account_number','worker_bank_details.socso_number','worker_bank_details.created_by','worker_bank_details.modified_by', 'worker_bank_details.created_at', 'worker_bank_details.updated_at', 'worker_bank_details.deleted_at')->find($request['id']);
    }

    /**
     * List the worker bank detail
     *
     * @param $request
     *        worker_id (int) ID of the worker
     *        search_param (string) search parameter
     *
     * @return mixed Returns The paginated list of bank detail
     */
    public function listBankDetails($request) : mixed
    {
        $validationResult = $this->validateListBankDetailsRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }
        return $this->workerBankDetails
        ->where('worker_bank_details.worker_id', $request['worker_id'])
        ->where(function ($query) use ($request) {
            if (!empty($request['search_param'])) {
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
     *        id (int) ID of the bank record
     *
     * @return mixed Returns an array with two keys: 'isDeleted' and 'message'
     */
    public function deleteBankDetails($request): mixed
    {
        $request = $this->enrichRequestWithUserDetails($request);

        $workerBankDetail = $this->getWorkerBankDetails($request);

        if(is_null($workerBankDetail)){
            return [
                "isDeleted" => false,
                "message" => self::MESSAGE_DATA_NOT_FOUND
            ];
        }
        $workerBankDetail->delete();
        return [
            "isDeleted" => true,
            "message" => self::MESSAGE_DELETED_SUCCESSFULLY
        ];
    }

    /**
     * Get the worker bank detail count based on the given request data.
     *
     * @param array $request The request data containing the worker id.
     *
     * @return int Returns the worker bank detail count
     */
    private function getWorkerBankDetailsCount($request)
    {
        return $this->workerBankDetails::where('worker_id', $request['worker_id'])->count();
    }

    /**
     * Get the worker bank detail based on the given request data.
     *
     * @param array $request The request data containing the company ID and id.
     * @return mixed Returns the worker bank detail matching the given company ID and id,
     *               or null if no matching worker bank detail is found.
     */
    private function getWorkerBankDetails($request)
    {
        return $this->workerBankDetails->join('workers', function ($join) use ($request) {
            $join->on('workers.id', '=', 'worker_bank_details.worker_id')
                 ->whereIn('workers.company_id', $request['company_id']);
        })->select('worker_bank_details.id')->find($request['id']);
    }

    /**
     * add attachment for worker
     *
     * @param $request
     *        worker_id (int) ID of the worker
     *        attachment (file) uploading file
     *
     * @return bool|array Returns true if the file is uploaded successfully, otherwise returns an array with error details
     */
    public function addAttachment($request): bool|array
    {
        $params = $request->all();
        $user = $this->getAuthenticatedUser();
        $params['created_by'] = $user['id'];
        $params['company_id'] = $user['company_id'];

        $validationResult = $this->validateAddAttachmentRequest($request);
        if (is_array($validationResult)) {
            return $validationResult;
        }

        $workerExists = $this->getWorkerByCompanyId($params);
        if(is_null($workerExists)) {
            return self::ERROR_WORKER;
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
        } else {
            return false;
        }
    }

    /**
     * Get the worker based on the given request data.
     *
     * @param array $request The request data containing the company ID and worker id.
     * @return mixed Returns the worker matching the given company ID and worker id,
     *               or null if no matching worker is found.
     */
    private function getWorkerByCompanyId($request)
    {
        return $this->workers::where('company_id', $request['company_id'])->find($request['worker_id']);
    }

    /**
     * List the worker attachment
     *
     * @param $request
     *        worker_id (int) ID of the worker
     *
     * @return mixed Returns The paginated list of worker attachment
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
     * Delete the worker attachment
     *
     * @param $request
     *        attachment_id (int) ID of the attachment
     *
     * @return bool Returns true if the file is deleted successfully, otherwise returns false
     */
    public function deleteAttachment($request): bool
    {
        $request = $this->enrichRequestWithUserDetails($request);

        $data = $this->workerAttachments->join('workers', function ($join) use ($request) {
            $join->on('workers.id', '=', 'worker_attachments.file_id')
                 ->whereIn('workers.company_id', $request['company_id']);
        })->select('worker_attachments.id')->find($request['id']);
        if(is_null($data)) {
            return false;
        }
        $data->delete();
        return true;
    }
    /**
     * Import the workers
     *
     * @param $request The request data containing import worker details
     *
     * @return mixed Returns true if the file is imported successfully, otherwise returns an array with error details
     */
    public function import($request, $file): mixed
    {
        $params = $request->all();
        $user = $this->getAuthenticatedUser();
        $params['created_by'] = $user['id'];
        $params['company_id'] = $user['company_id'];
        $params['user_type'] = $user['user_type'];

        $workerBulkUpload = $this->createworkerBulkUpload($params);

        $rows = Excel::toArray(new CommonWorkerImport($params, $workerBulkUpload), $file);
        $this->workerBulkUpload->where('id', $workerBulkUpload->id)->update(['actual_row_count' => count($rows[0])]);
        Excel::import(new CommonWorkerImport($params, $workerBulkUpload), $file);
        return true;
    }

    /**
     * create worker bulk upload.
     *
     * @param array $request
     *              company_id (int) ID of the company
     *              created_by (int) ID of the created user
     *              user_type (string) Type of the user
     *
     * @return mixed  Returns the created worker bulk upload record.
     */
    private function createworkerBulkUpload($request): mixed
    {
        return $this->workerBulkUpload->create([
            'name' => self::WORKER_BULK_UPLOAD,
            'type' => self::WORKER_BULK_UPLOAD,
            'module_type' => self::MODULE_TYPE_WORKERS,
            'company_id' => $request['company_id'],
            'created_by' => $request['created_by'],
            'modified_by' => $request['created_by'],
            'user_type' => $request['user_type']
        ]
    );
    }

    /**
     * List the worker import history
     *
     * @param $request
     *        company_id (array) ID of the user company
     *
     * @return mixed Returns The paginated list of worker import history
     */
    public function importHistory($request) : mixed
    {
        $user = JWTAuth::parseToken()->authenticate();
        $request['company_id'] = $this->authServices->getCompanyIds($user);

        return $this->workerBulkUpload
        ->select('id', 'actual_row_count', 'total_success', 'total_failure', 'process_status', 'created_at')
        ->where('module_type', self::MODULE_TYPE_WORKERS)
        ->where('process_status', self::PROCESS_STATUS)
        ->whereNotNull('failure_case_url')
        ->whereIn('company_id', $request['company_id'])
        ->orderBy('id', 'desc')
        ->paginate(Config::get('services.paginate_row'));
    }
    /**
     * Shows the worker import failure export file
     *
     * @param $request
     *
     * @return array Returns an array with key: file_url
     */
    public function failureExport($request): array
    {
        $workerBulkUpload = $this->workerBulkUpload->findOrFail($request['bulk_upload_id']);
        if($workerBulkUpload->process_status != self::PROCESS_STATUS || is_null($workerBulkUpload->failure_case_url)) {
            return self::ERROR_QUEUE;
        }
        return [
            'file_url' => $workerBulkUpload->failure_case_url
        ];
    }
    /**
     * Process the worker import failure case
     *
     * @return bool
     */
    public function prepareExcelForFailureCases(): bool
    {
        $ids = [];
        $data = [];
        $bulkUploads = $this->getWorkerBulkUploadRows();
        foreach($bulkUploads as $bulkUpload) {
            if($bulkUpload['actual_row_count'] == ($bulkUpload['total_success'] + $bulkUpload['total_failure'])) {
                array_push($ids, $bulkUpload['id']);
                $data[$bulkUpload['id']]['module_type'] = $bulkUpload['module_type'];
            }
        }
        $this->updateWorkerBulkUploadStatus($ids);
        $this->createWorkerFailureCasesDocument($ids,$data);
        return true;
    }

    /**
     * Get the worker bulk upload rows.
     *
     * @return mixed Retruns the worker bulk upload record rows
     */
    private function getWorkerBulkUploadRows(): mixed
    {
        return $this->workerBulkUpload
        ->where( function ($query) {
            $query->whereNull('process_status')
            ->orWhereNull('failure_case_url');
        })
        ->select('id', 'total_records', 'total_success', 'total_failure', 'actual_row_count', 'module_type')
        ->get()->toArray();
    }

    /**
     * Update the status of worker bulk upload rows.
     *
     * @param array $ids - Id of the worker bulk upload record
     * @return void
     */
    private function updateWorkerBulkUploadStatus($ids)
    {
        $this->workerBulkUpload->whereIn('id', $ids)->update(['process_status' => self::PROCESS_STATUS]);
    }

    /**
     * create worker failure cases document.
     *
     * @param array $ids Id of the worker bulk upload record
     * @param array $data worker module type
     * @return void
     */
    private function createWorkerFailureCasesDocument($ids,$data)
    {
        foreach($ids as $id) {
            $moduleType = isset($data[$id]['module_type']) ? $data[$id]['module_type'] : '';
            $fileName = "FailureCases" . $id . ".xlsx";
            $filePath = '/FailureCases/Workers/' . $fileName;
            if ($moduleType == self::MODULE_TYPE_WORKERBIODATA){
                Excel::store(new WorkerBiodataFailureExport($id), $filePath, 'linode');
            }
            if ($moduleType == self::MODULE_TYPE_WORKERS){
                Excel::store(new FailureExport($id), $filePath, 'linode');
            }
            $fileUrl = $this->storage::disk('linode')->url($filePath);
            $this->workerBulkUpload->where('id', $id)->update(['failure_case_url' => $fileUrl]);
        }
    }
}
