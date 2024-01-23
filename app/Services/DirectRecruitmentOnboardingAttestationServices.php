<?php

namespace App\Services;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use App\Models\OnboardingAttestation;
use App\Models\OnboardingDispatch;
use App\Models\OnboardingEmbassy;
use App\Models\EmbassyAttestationFileCosting;
use App\Models\DirectRecruitmentOnboardingCountry;
use App\Services\DirectRecruitmentOnboardingCountryServices;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\Facades\JWTAuth;
use Carbon\Carbon;
use App\Models\User;

class DirectRecruitmentOnboardingAttestationServices
{

    public const REQUEST_COMPANY_ID = 'company_id';
    public const REQUEST_APPLICATION_ID = 'application_id';
    public const REQUEST_ONBOARDING_COUNTRY_ID = 'onboarding_country_id';
    public const REQUEST_ONBOARDING_AGENT_ID = 'onboarding_agent_id';
    public const REQUEST_KSM_REFERENCE_NUMBER = 'ksm_reference_number';
    public const REQUEST_ONBOARDING_ATTESTATION_ID = 'onboarding_attestation_id';

    public const REQUEST_ITEM_NAME = 'Attestation Submission';
    public const REQUEST_ATTESTATION_STATUS = 'Pending';

    public const ONBOARDING_STATUS = 2;
    public const ONBOARDING_STATUS_AGENT_ADDED = 3;
    public const DEFAULT_INT_VALUE = 0;
    public const STATUS_ACTIVE = 1;
    public const STATUS_IN_ACTIVE = 0;
    public const REFERENCE_NUMBER_PREFIX = 'JO00000';
    public const DISPATCH_NOTIFICATION_TYPE = 'Dispatches';
    public const DISPATCH_NOTIFICATION_TITLE = 'Dispatches';
    public const DISPATCH_NOTIFICATION_MESSAGE = ' Dispatch is Assigned';
    public const MESSAGE_DELETED_NOT_FOUND = 'Data not found';
    public const MESSAGE_DELETED_SUCCESSFULLY = 'Deleted Successfully';

    /**
     * @var OnboardingAttestation
     */
    private OnboardingAttestation $onboardingAttestation;

    /**
     * @var OnboardingDispatch
     */
    private OnboardingDispatch $onboardingDispatch;

    /**
     * @var OnboardingEmbassy
     */
    private OnboardingEmbassy $onboardingEmbassy;

    /**
     * @var EmbassyAttestationFileCosting
     */
    private EmbassyAttestationFileCosting $embassyAttestationFileCosting;

    /**
     * @var DirectRecruitmentOnboardingCountryServices
     */
    private DirectRecruitmentOnboardingCountryServices $directRecruitmentOnboardingCountryServices;

    /**
     * @var Storage
     */
    private Storage $storage;
    /**
     * @var DirectRecruitmentExpensesServices
     */
    private DirectRecruitmentExpensesServices $directRecruitmentExpensesServices;

    /**
     * @var DirectRecruitmentOnboardingCountry
     */
    private DirectRecruitmentOnboardingCountry $directRecruitmentOnboardingCountry;
    /**
     * @var NotificationServices
     */
    private NotificationServices $notificationServices;

    /**
     * DirectRecruitmentOnboardingAttestationServices constructor method.
     * 
     * @param OnboardingAttestation $onboardingAttestation The onBoarding Attestation Object;
     * @param OnboardingDispatch $onboardingDispatch The onBoarding Dispatch Object;
     * @param OnboardingEmbassy $onboardingEmbassy The onBoarding Embassy Object;
     * @param EmbassyAttestationFileCosting $embassyAttestationFileCosting The Embassy Attestation File Costing Object;
     * @param DirectRecruitmentOnboardingCountryServices $directRecruitmentOnboardingCountryServices The Direct Recruitment OnBoarding Country Services;
     * @param DirectRecruitmentOnboardingCountry $directRecruitmentOnboardingCountry The Direct Recruitment OnBoarding Country Object;
     * @param Storage $storage The storage ;
     * @param DirectRecruitmentExpensesServices $directRecruitmentExpensesServices The Direct Recruitment Expenses services
     * @param NotificationServices $notificationServices The Notification services
     */

    public function __construct(
        OnboardingAttestation $onboardingAttestation, 
        OnboardingDispatch $onboardingDispatch, 
        OnboardingEmbassy $onboardingEmbassy, 
        EmbassyAttestationFileCosting $embassyAttestationFileCosting, 
        DirectRecruitmentOnboardingCountryServices $directRecruitmentOnboardingCountryServices, 
        DirectRecruitmentOnboardingCountry $directRecruitmentOnboardingCountry, 
        Storage $storage, 
        DirectRecruitmentExpensesServices $directRecruitmentExpensesServices, 
        NotificationServices $notificationServices
    )
    {
        $this->onboardingAttestation = $onboardingAttestation;
        $this->onboardingDispatch = $onboardingDispatch;
        $this->onboardingEmbassy = $onboardingEmbassy;
        $this->embassyAttestationFileCosting = $embassyAttestationFileCosting;
        $this->directRecruitmentOnboardingCountryServices = $directRecruitmentOnboardingCountryServices;
        $this->storage = $storage;
        $this->directRecruitmentExpensesServices = $directRecruitmentExpensesServices;
        $this->directRecruitmentOnboardingCountry = $directRecruitmentOnboardingCountry;
        $this->notificationServices = $notificationServices;
    }
    /**
     * Validates the input and returns the errors if validation fails.
     *
     * @return array The validation error messages if validation fails, otherwise false.
     */
    public function createValidation(): array
    {
        return [
            'application_id' => 'required',
            'onboarding_country_id' => 'required',
            'ksm_reference_number' => 'required'
        ];
    }
    /**
     * Validates the input and returns the errors if validation fails.
     *
     * @return array The validation error messages if validation fails, otherwise false.
     */
    public function updateValidation(): array
    {
        return [
            'id' => 'required'
        ];
    }
    /**
     * Validates the input and returns the errors if validation fails.
     *
     * @return array The validation error messages if validation fails, otherwise false.
     */
    public function updateDispatchValidation(): array
    {
        return [
            'onboarding_attestation_id' => 'required',
            'date' => 'required|date|date_format:Y-m-d',
            'time' => 'required',
            'reference_number' => 'required',
            'employee_id' => 'required',
            'from' => 'required',
            'calltime' => 'required|date|date_format:Y-m-d',
            'area' => 'required',
            'employer_name' => 'required',
            'phone_number' => 'required'
        ];
    }
    /**
     * Validates the input and returns the errors if validation fails.
     *
     * @return array The validation error messages if validation fails, otherwise false.
     */
    public function uploadEmbassyFileValidation(): array
    {
        return [
            'onboarding_attestation_id' => 'required',
            'embassy_attestation_id' => 'required',
        ];
    }
    /**
     * Returns a paginated list of on Boarding Attestation details with direct recruitment application details.
     *
     * @param array $request The request data containing company id,  application_id, onboarding_country_id
     * @return mixed \Illuminate\Pagination\LengthAwarePaginator The paginated list of direct recruitment onboarding agent with direct recruitment application details.
     */ 
    public function list($request): mixed
    {
        return $this->onboardingAttestation
        ->join('directrecruitment_applications', function ($join) use($request) {
            $join->on('onboarding_attestation.application_id', '=', 'directrecruitment_applications.id')
                ->whereIn('directrecruitment_applications.company_id', $request[self::REQUEST_COMPANY_ID]);
        })
        ->where([
            ['onboarding_attestation.application_id', $request[self::REQUEST_APPLICATION_ID]],
            ['onboarding_attestation.onboarding_country_id', $request[self::REQUEST_ONBOARDING_COUNTRY_ID]],
        ])
        ->select('onboarding_attestation.id', 'onboarding_attestation.application_id', 'onboarding_attestation.onboarding_country_id', 'onboarding_attestation.onboarding_agent_id', 'onboarding_attestation.ksm_reference_number', 'onboarding_attestation.item_name', 'onboarding_attestation.status', 'onboarding_attestation.submission_date', 'onboarding_attestation.collection_date', 'onboarding_attestation.created_at', 'onboarding_attestation.updated_at')
        ->orderBy('onboarding_attestation.id', 'desc')
        ->paginate(Config::get('services.paginate_row'));
    }
    /**
     * Show the Boarding Attestation details.
     *
     * @param array $request The request data containing id, company id
     * @return mixed Returns details of  direct recruitment on boarding Attestation.
     */
    public function show($request): mixed
    {
        return $this->onboardingAttestation->join('directrecruitment_applications', function ($join) use($request) {
            $join->on('onboarding_attestation.application_id', '=', 'directrecruitment_applications.id')
                ->whereIn('directrecruitment_applications.company_id', $request[self::REQUEST_COMPANY_ID]);
            })
            ->where('onboarding_attestation.id', $request['id'])
            ->first('onboarding_attestation.*');
    }

    /**
     * Get the onBoarding Attestation details for the given input details
     * 
     * - application_id: Direct Recruitment Application Id
     * - onboarding_country_id: Direct Recruitment Onboarding Country Id.
     * - onboarding_agent_id: on Boarding Agent Id.
     * - ksm_reference_number: KSM Reference Number.
     *
     * @param array $request The data used to get the Direct Recruitment Attestation details.
     * Return mixed the Attestation object
     */
    public function checkOnboardingAttestationData(mixed $request): mixed
    {
        return $this->onboardingAttestation->where([
            ['application_id', $request[self::REQUEST_APPLICATION_ID]],
            ['onboarding_country_id', $request[self::REQUEST_ONBOARDING_COUNTRY_ID]],
            ['onboarding_agent_id', $request[self::REQUEST_ONBOARDING_AGENT_ID]],
            ['ksm_reference_number', $request[self::REQUEST_KSM_REFERENCE_NUMBER]]
        ])->first(['id', 'application_id', 'onboarding_country_id']);
    }

    /**
     * Create a new Direct Recruitment onboarding Attestation .
     *
     * @param array $inputData The data used to create the Direct Recruitment Onboarding Attestation.
     * The array should contain the following keys:
     * - application_id: Direct Recruitment Application Id.
     * - onboarding_country_id: OnBoarding Country Id.
     * - onboarding_agent_id: On Boarding Agent Id.
     * - ksm_reference_number: KSM Reference Number.
     * - item_name: Item Name.
     * - status: Direct Recruitment Onboarding Attestation status .
     * - created_by: The user who created the Direct Recruitment Onboarding Attestation.
     * - modified_by: The user who modified the Direct Recruitment Onboarding Attestation.
     *
     * @return mixed The newly created Direct Recruitment Onboarding Attestation.
     */
    public function createDirectRecruitmentOnboardingAttestation(array $request):void
    {
        $this->onboardingAttestation->create([
            'application_id' => $request[self::REQUEST_APPLICATION_ID] ?? 0,
            'onboarding_country_id' => $request[self::REQUEST_ONBOARDING_COUNTRY_ID] ?? 0,
            'onboarding_agent_id' => $request[self::REQUEST_ONBOARDING_AGENT_ID] ?? 0,
            'ksm_reference_number' => $request[self::REQUEST_KSM_REFERENCE_NUMBER] ?? '',
            'item_name' => self::REQUEST_ITEM_NAME,
            'status' => self::REQUEST_ATTESTATION_STATUS,
            'created_by' => $request['created_by'] ?? 0,
            'modified_by' => $request['created_by'] ?? 0
        ]);
    }

    /**
     * Create a new Direct Recruitment Onboarding Attestation .
     *
     * @param array $request The data used to create the Direct Recruitment Onboarding Attestation.
     * The array should contain the following keys:
     * - application_id: Direct Recruitment Application Id.
     * - onboarding_country_id: OnBoarding Country Id.
     * - onboarding_agent_id: on Boarding Agent Id.
     * - ksm_reference_number: KSM Reference Number.
     * - status: Direct Recruitment Onboarding Attestation status .
     * - created_by: The user who created the Direct Recruitment Onboarding Attestation.
     * - modified_by: The user who modified the Direct Recruitment Onboarding Attestation.
     *
     * * @return bool|array Returns an array with the following keys:
     * - "validate": An array of validation errors, if any.
     * - "true": A boolean returns true if attestatiion created successfully
     * - "false": A boolean returns false if attestatiion created failed
     *
     */  
    public function create($request): bool|array
    {
        $onboardingAttestation = $this->checkOnboardingAttestationData($request);

        if(is_null($onboardingAttestation)){
            $validator = Validator::make($request, $this->createValidation());
            if($validator->fails()) {
                return [
                    'error' => $validator->errors()
                ];
            }
            
            $this->createDirectRecruitmentOnboardingAttestation($request);

            return true;
        }else{
            return false;
        }
        
    }
    /**
     * Get the onBoardingAttestation details for the given input details
     * 
     * @param array $request The data used to get the onBoardingAttestation details
     * - id: onBoardingAttestation id
     * - company_id: Direct Recruitment company id
     *
     * @return object onBoardingAttestation details
     */
    public function getOnboardingAttestation(array $request): object
    {
        return $this->onboardingAttestation
        ->join('directrecruitment_applications', function ($join) use($request) {
            $join->on('onboarding_attestation.application_id', '=', 'directrecruitment_applications.id')
            ->where('directrecruitment_applications.company_id', $request[self::REQUEST_COMPANY_ID]);
        })
        ->select('onboarding_attestation.id', 'onboarding_attestation.application_id', 'onboarding_attestation.onboarding_country_id', 'onboarding_attestation.onboarding_agent_id', 'onboarding_attestation.ksm_reference_number', 'onboarding_attestation.submission_date', 'onboarding_attestation.collection_date', 'onboarding_attestation.item_name', 'onboarding_attestation.status', 'onboarding_attestation.file_url', 'onboarding_attestation.remarks', 'onboarding_attestation.created_by', 'onboarding_attestation.modified_by', 'onboarding_attestation.created_at', 'onboarding_attestation.updated_at', 'onboarding_attestation.deleted_at')
        ->find($request['id']);
    }
    /**
     * Get the onBoardingAttestation collected count on the input of application_id, onBoarding_country_id
     * 
     * @param int $applicationId, int $onboardingCountryId The data used to get the collected count in onBoardingAttestation
     * - applicationId (int): application id
     * - onboardingCountryId (int): onBoarding Country id
     *
     * @return int onBoardingAttestation details
     */
    public function getOnboardingAttestationCollectedCount($applicationId, $onboardingCountryId): int
    {
        return $this->onboardingAttestation->where('application_id', $applicationId)
        ->where('onboarding_country_id', $onboardingCountryId)
        ->where('status', 'Collected')
        ->count();
    }
    /**
     * Get the agent quota for the given input $requestApplicationId, $requestOnboardingCountryId
     * 
     * - requestApplicationId: Direct Recruitment Application Id
     * - requestOnboardingCountryId: Direct Recruitment Onboarding Country Id.
     *
     * @param int $requestApplicationId, int $requestOnboardingCountryId
     * Return void
     */
    public function onboardingStatusUpdate(int $requestApplicationId, int $requestOnboardingCountryId): void
    {
        $onBoardingStatus['application_id'] = $requestApplicationId;
        $onBoardingStatus['country_id'] = $requestOnboardingCountryId;
        $onBoardingStatus['onboarding_status'] = self::ONBOARDING_STATUS_AGENT_ADDED; //Agent Added
        $this->directRecruitmentOnboardingCountryServices->onboarding_status_update($onBoardingStatus);
    }
    /**
     * Update a new Direct Recruitment Onboarding Attestation .
     *
     * @param array $request The data used to update the Direct Recruitment Onboarding Attestation.
     * The array should contain the following keys:
     * - company_id: Direct Recruitment application company_id.
     * - submission_date: attestation submission date
     * - collection_date: attestation collection date
     * - application_id: Direct Recruitment application id
     * - onboarding_country_id: Direct Recruitment Onboarding country id.
     * - file_url: file Attachment.
     * - remarks: attestation remarks
     * - status: attestation status
     * - modified_by: The user who modified the Direct Recruitment Onboarding Attestation.
     *
     * * @return bool|array Returns an array with the following keys:
     * - "validate": An array of validation errors, if any.
     * - "InvalidUser": A boolean returns true if user is invalid.
     * - "true": A boolean returns true if attestatiion updated successfully
     *
     */  
    public function update($request): bool|array
    {
        $validator = Validator::make($request, $this->updateValidation());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }
    
        $onboardingAttestation = $this->getOnboardingAttestation($request);
        
        if(is_null($onboardingAttestation)) {
            return[
                'InvalidUser' => true
            ];
        }
        
        if (!empty($request['submission_date'])) {
            $request['status'] = 'Submitted';
            $onboardingAttestation->submission_date =  $request['submission_date'];
        }
        if (!empty($request['collection_date'])) {
            $request['status'] = 'Collected';
            $onboardingAttestation->collection_date =  $request['collection_date'];

            $attestationCount = $this->getOnboardingAttestationCollectedCount($onboardingAttestation->application_id, $onboardingAttestation->onboarding_country_id); 

            if($attestationCount == 0) {
                $this->onboardingStatusUpdate($request[self::REQUEST_APPLICATION_ID], $request[self::REQUEST_ONBOARDING_COUNTRY_ID]);  
            }
        }
        $onboardingAttestation->file_url =  $request['file_url'] ?? $onboardingAttestation->file_url;
        $onboardingAttestation->remarks =  $request['remarks'] ?? $onboardingAttestation->remarks;
        $onboardingAttestation->status =  $request['status'] ?? $onboardingAttestation->status;
        $onboardingAttestation->modified_by =  $request['modified_by'] ?? $onboardingAttestation->modified_by;
        $onboardingAttestation->save();
        return true;
    }
    /**
     * Show the direct recruitment on boarding agent details.
     *
     * @param array $request The request data containing company_id, onboarding_attestation_id
     * @return mixed Returns details of direct recruitment on-boarding agent details.
     */
    public function showDispatch($request): mixed
    {
        return $this->onboardingDispatch
        ->join('onboarding_attestation', 'onboarding_attestation.id', 'onboarding_dispatch.onboarding_attestation_id')
        ->join('directrecruitment_applications', function ($join) use($request) {
            $join->on('onboarding_attestation.application_id', '=', 'directrecruitment_applications.id')
            ->whereIn('directrecruitment_applications.company_id', $request[self::REQUEST_COMPANY_ID]);
        })->where('onboarding_dispatch.onboarding_attestation_id', $request[self::REQUEST_ONBOARDING_ATTESTATION_ID])
        ->select('onboarding_dispatch.*')
        ->get();
    }

    /**
     * Get the onBoardingAttestation details for the given input details
     * 
     * @param array $request The data used to get the onBoardingAttestation details
     * - id: onBoardingAttestation id
     * - company_id: Direct Recruitment company id
     *
     * @return object onBoardingAttestation data
     */
    public function getOnboardingAttestationData(array $request): object
    {

        return $this->onboardingAttestation->join('directrecruitment_applications', function ($join) use($request) {
            $join->on('onboarding_attestation.application_id', '=', 'directrecruitment_applications.id')
                ->where('directrecruitment_applications.company_id', $request[self::REQUEST_COMPANY_ID]);
            })
            ->where('onboarding_attestation.id', $request[self::REQUEST_ONBOARDING_ATTESTATION_ID])
            ->select('onboarding_attestation.id', 'onboarding_attestation.application_id', 'onboarding_attestation.onboarding_country_id', 'onboarding_attestation.onboarding_agent_id', 'onboarding_attestation.ksm_reference_number', 'onboarding_attestation.submission_date', 'onboarding_attestation.collection_date', 'onboarding_attestation.item_name', 'onboarding_attestation.status', 'onboarding_attestation.file_url', 'onboarding_attestation.remarks', 'onboarding_attestation.created_by', 'onboarding_attestation.modified_by', 'onboarding_attestation.created_at', 'onboarding_attestation.updated_at', 'onboarding_attestation.deleted_at')->first();
    }

    /**
     * Create a new Dispatch .
     *
     * @param array $request The data used to create the Dispatch.
     * The array should contain the following keys:
     * 
     * - onboarding_attestation_id: on boarding attestation id
     * - date: Dispath date
     * - time: Dispath time
     * - employee_id: employee id
     * - from: from user details
     * - calltime: call Time
     * - area: area details
     * - employer_name: employer mapped with this dispatch
     * - phone_number: contact number
     * - remarks: Remarks on the updating the dispatch
     * - created_by: The user who created the Dispatch.
     * - modified_by: The user who modified the Dispatch.
     *
     * @param array $request to create a new Dispatch .
     * @return void new Dispatch detail.
     * 
     */
    public function onBoardingDispatchCreate(array $request):void
    {
        $this->onboardingDispatch->create([
            'onboarding_attestation_id' => $request[self::REQUEST_ONBOARDING_ATTESTATION_ID] ?? 0,
            'date' => $request['date'] ?? null,
            'time' => $request['time'] ?? '',
            'reference_number' => $request['reference_number'],
            'employee_id' => $request['employee_id'] ?? '',
            'from' => $request['from'] ?? '',
            'calltime' => $request['calltime'] ?? null,
            'area' => $request['area'] ?? '',
            'employer_name' => $request['employer_name'] ?? '',
            'phone_number' => $request['phone_number'] ?? '',
            'remarks' => $request['remarks'] ?? '',
            'created_by' =>  $request['created_by'] ?? 0,
            'modified_by' =>  $request['created_by'] ?? 0
        ]);
    }

    /**
     * Update a Dispatch on the given request
     *
     * @param array $request The data used to update the Dispatch.
     * The array should contain the following keys:
     * 
     * - date: Dispath date
     * - time: Dispath time
     * - employee_id: employee id
     * - from: from user details
     * - calltime: call Time
     * - area: area details
     * - employer_name: employer mapped with this dispatch
     * - phone_number: contact number
     * - remarks: Remarks on the updating the dispatch
     * - modified_by: The user who modified the Dispatch.
     *
     * @param array $request to update a Dispatch .
     * @return void update Dispatch detail
     * 
     */
    public function onBoardingDispatchUpdate($onboardingDispatch, array $request):void
    {
        $onboardingDispatch->update([
            'date' => $request['date'] ?? null,
            'time' => $request['time'] ?? '',
            'employee_id' => $request['employee_id'] ?? '',
            'from' => $request['from'] ?? '',
            'calltime' => $request['calltime'] ?? null,
            'area' => $request['area'] ?? '',
            'employer_name' => $request['employer_name'] ?? '',
            'phone_number' => $request['phone_number'] ?? '',
            'remarks' => $request['remarks'] ?? '',
            'modified_by' =>  $request['created_by'] ?? 0
        ]);
    }

    /**
     * Create a new Dispatch Notification .
     *
     * @param array $request The data used to create the Dispatch Notification.
     * The array should contain the following keys:
     * 
     * - employee_id: employee id
     * - company_id:company id
     * - reference_number: dispatch reference number
     * - created_by: The user who created the Dispatch Notification.
     * - modified_by: The user who modified the Dispatch Notification.
     * 
     * @param array $request to create a new Dispatch Notification.
     * @return void new Dispatch Notification.
     * 
     */
    public function createDispatchNotification($getUser, array $request):void
    {
        $NotificationParams['user_id'] = $request['employee_id'];
        $NotificationParams['from_user_id'] = $request['created_by'];
        $NotificationParams['type'] = 'Dispatches';
        $NotificationParams['title'] = 'Dispatches';
        $NotificationParams['message'] = $request['reference_number'].' Dispatch is Assigned';
        $NotificationParams['status'] = self::STATUS_ACTIVE;
        $NotificationParams['read_flag'] = self::STATUS_IN_ACTIVE;
        $NotificationParams['created_by'] = $request['created_by'];
        $NotificationParams['modified_by'] = $request['created_by'];
        $NotificationParams['company_id'] = $request[self::REQUEST_COMPANY_ID];
        $this->notificationServices->insertDispatchNotification($NotificationParams);
        dispatch(new \App\Jobs\RunnerNotificationMail(Config::get('database.connections.mysql.database'), $getUser,$NotificationParams['message']))->onQueue(Config::get('services.RUNNER_NOTIFICATION_MAIL'))->onConnection(Config::get('services.QUEUE_CONNECTION'));        
    }
    
    /**
     * Update a dispatch details
     *
     * @param array $request The data used to update the dispatch.
     * The array should contain the following keys:
     * - company_id: Direct Recruitment application company_id.
     * - onboarding_attestation_id: on boarding attestation id
     * - date: Dispath date
     * - time: Dispath time
     * - employee_id: employee id
     * - from: from user details
     * - calltime: call Time
     * - area: area details
     * - employer_name: employer mapped with this dispatch
     * - phone_number: contact number
     * - remarks: Remarks on the updating the dispatch
     *
     * * @return bool|array Returns an array with the following keys:
     * - "validate": An array of validation errors, if any.
     * - "InvalidUser": A boolean returns true if user is invalid.
     * - "true": A boolean returns true if attestatiion updated successfully
     *
     */ 
    public function updateDispatch($request): bool|array
    {

        $attestationCheck = $this->getOnboardingAttestationData($request);
        if(is_null($attestationCheck)) {
            return [
                'InvalidUser' => true
            ];
        }
        $onboardingDispatch = $this->onboardingDispatch->where(
            'onboarding_attestation_id', $request['onboarding_attestation_id']
        )->first(['id', 'onboarding_attestation_id', 'date', 'time', 'reference_number', 'employee_id', 'from', 'calltime', 'area', 'employer_name', 'phone_number', 'remarks']);

        $request['reference_number'] = self::REFERENCE_NUMBER_PREFIX.$this->onboardingDispatch->count() + 1;

        $validator = Validator::make($request, $this->updateDispatchValidation());
            if($validator->fails()) {
                return [
                    'error' => $validator->errors()
                ];
            }
        
        if(is_null($onboardingDispatch)){
            $this->onBoardingDispatchCreate($request);
        }else{
            $this->onBoardingDispatchUpdate($onboardingDispatch, $request);
            
        }

        $getUser = $this->getUser($request['employee_id']);
        if($getUser){
            $this->createDispatchNotification($getUser, $request);
        }
        
        return true;
    }
    /**
     * Returns a paginated list of embassy attestation file costing with onboarding embassy details.
     *
     * @param array $request The request data containing company id, onboarding_attestation_id, country_id
     * @return mixed \Illuminate\Pagination\LengthAwarePaginator The paginated list of embassy attestation file costing with onboarding embassy details.
     * - "InvalidUser": A boolean returns true if user is invalid.
     */  
    public function listEmbassy($request): mixed
    {
        $onboardingAttestation = $this->onboardingAttestation
        ->leftJoin('directrecruitment_onboarding_countries', 'directrecruitment_onboarding_countries.id', 'onboarding_attestation.onboarding_country_id')
        ->join('directrecruitment_applications', function ($join) use($request) {
            $join->on('onboarding_attestation.application_id', '=', 'directrecruitment_applications.id')
                ->whereIn('directrecruitment_applications.company_id', $request[self::REQUEST_COMPANY_ID]);
        })
        ->where('onboarding_attestation.id', $request[self::REQUEST_ONBOARDING_ATTESTATION_ID])
        ->select('directrecruitment_onboarding_countries.country_id')
        ->distinct('directrecruitment_onboarding_countries.country_id')
        ->get();
        if(count($onboardingAttestation) == self::DEFAULT_INT_VALUE) {
            return [
                'InvalidUser' => true
            ];
        }
        $request['country_id'] = $onboardingAttestation[0]['country_id'] ?? self::DEFAULT_INT_VALUE;

        return $this->embassyAttestationFileCosting
        ->leftJoin('onboarding_embassy', function ($join) use ($request) {
            $join->on('onboarding_embassy.embassy_attestation_id', '=', 'embassy_attestation_file_costing.id')
            ->where([
                ['onboarding_embassy.onboarding_attestation_id', $request[self::REQUEST_ONBOARDING_ATTESTATION_ID]],
                ['onboarding_embassy.deleted_at', null],
            ]);
        })
        ->where([
            ['embassy_attestation_file_costing.country_id', $request['country_id']]
        ])
        ->select('embassy_attestation_file_costing.id', 'embassy_attestation_file_costing.country_id', 'embassy_attestation_file_costing.title', 'embassy_attestation_file_costing.amount', 'onboarding_embassy.id as onboarding_embassy_id', 'onboarding_embassy.file_name as onboarding_embassy_file_name', 'onboarding_embassy.file_url as onboarding_embassy_file_url', 'onboarding_embassy.amount as onboarding_embassy_amount')
        ->distinct('embassy_attestation_file_costing.id', 'embassy_attestation_file_costing.country_id')
        ->orderBy('embassy_attestation_file_costing.id','DESC')
        ->paginate(Config::get('services.paginate_row'));
    }
    /**
     * Returns a embassy attestation file costing with onboarding embassy details.
     *
     * @param array $request The request data containing company id, onboarding_attestation_id, country_id
     * @return mixed show embassy attestation file costing with onboarding embassy details.
     * - "InvalidUser": A boolean returns true if user is invalid.
     */   
    public function showEmbassyFile($request): mixed
    {
        $attestationCheck = $this->onboardingAttestation->join('directrecruitment_applications', function ($join) use($request) {
            $join->on('onboarding_attestation.application_id', '=', 'directrecruitment_applications.id')
                ->whereIn('directrecruitment_applications.company_id', $request[self::REQUEST_COMPANY_ID]);
            })
            ->where('onboarding_attestation.id', $request[self::REQUEST_ONBOARDING_ATTESTATION_ID])
            ->first('onboarding_attestation.*');

        if(is_null($attestationCheck)) {
            return [
                'InvalidUser' => true
            ];
        }
        return $this->embassyAttestationFileCosting
        ->leftJoin('onboarding_embassy', function ($join) use ($request) {
            $join->on('onboarding_embassy.embassy_attestation_id', '=', 'embassy_attestation_file_costing.id')
            ->where([
                ['onboarding_embassy.onboarding_attestation_id', $request[self::REQUEST_ONBOARDING_ATTESTATION_ID]],
                ['onboarding_embassy.deleted_at', null],
            ]);
        })
        ->where([
            ['embassy_attestation_file_costing.id', $request['embassy_attestation_id']]
        ])
        ->select('embassy_attestation_file_costing.id', 'embassy_attestation_file_costing.country_id', 'embassy_attestation_file_costing.title', 'embassy_attestation_file_costing.amount', 'onboarding_embassy.id as onboarding_embassy_id', 'onboarding_embassy.file_name as onboarding_embassy_file_name', 'onboarding_embassy.file_url as onboarding_embassy_file_url', 'onboarding_embassy.amount as onboarding_embassy_amount')
        ->distinct('embassy_attestation_file_costing.id', 'embassy_attestation_file_costing.country_id')
        ->first();
    }

    /**
     * Uploads a embassy file
     *
     * @param array $request has the following keys 
     * The request data containing company id, onboarding_attestation_id, country_id
     * embassy_attestation_id
     * 
     * @return mixed show embassy attestation file costing with onboarding embassy details.
     * - "InvalidUser": A boolean returns true if user is invalid.
     * - "validate": An array of validation errors, if any.
     */  
    public function uploadEmbassyFile($request): bool|array
    {
        $params = $request->all();
        $user = JWTAuth::parseToken()->authenticate();
        $params['created_by'] = $user['id'];
        $params['company_id'] = $user[self::REQUEST_COMPANY_ID];

        $validator = Validator::make($request->toArray(), $this->uploadEmbassyFileValidation());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }

        $onboardingAttestation = $this->onboardingAttestation
        ->join('directrecruitment_applications', function ($join) use($params) {
            $join->on('onboarding_attestation.application_id', '=', 'directrecruitment_applications.id')
                ->where('directrecruitment_applications.company_id', $params[self::REQUEST_COMPANY_ID]);
        })
        ->where('onboarding_attestation.id', $params['onboarding_attestation_id'])
        ->first(['onboarding_attestation.application_id']);

        if(is_null($onboardingAttestation)) {
            return [
                'InvalidUser' => true
            ];
        }
        $request['application_id'] = isset($onboardingAttestation['application_id']) ? $onboardingAttestation['application_id'] : 0;

        $onboardingEmbassy = $this->onboardingEmbassy->where([
            ['onboarding_attestation_id', $request['onboarding_attestation_id']],
            ['embassy_attestation_id', $request['embassy_attestation_id']],
            ['deleted_at', null],
        ])->first(['id', 'onboarding_attestation_id', 'embassy_attestation_id', 'file_name', 'file_type', 'file_url', 'amount']);
        
        if (request()->hasFile('attachment')){
            foreach($request->file('attachment') as $file){
                $fileName = $file->getClientOriginalName();
                $filePath = '/directRecruitment/onboarding/embassyAttestationCosting/' . $fileName; 
                $linode = $this->storage::disk('linode');
                $linode->put($filePath, file_get_contents($file));
                $fileUrl = $this->storage::disk('linode')->url($filePath);

                if(is_null($onboardingEmbassy)){
                    $this->onboardingEmbassy::create([
                        "onboarding_attestation_id" => $request['onboarding_attestation_id'] ?? 0,
                        "embassy_attestation_id" => $request['embassy_attestation_id'] ?? 0,
                        "file_name" => $fileName,
                        "file_type" => 'Embassy Attestation Costing',
                        "file_url" =>  $fileUrl,
                        "amount" => $request['amount'] ?? 0,
                        "created_by" =>  $params['created_by'] ?? 0,
                        "modified_by" =>  $params['created_by'] ?? 0
                    ]); 
                }else{
                    $onboardingEmbassy->update([
                        "file_name" => $fileName,
                        "file_url" =>  $fileUrl,
                        "amount" => $request['amount'] ?? $onboardingEmbassy->amount,
                        "modified_by" =>  $params['created_by'] ?? 0
                    ]); 
                }
                // ADD OTHER EXPENSES - Onboarding - Attestation Costing
                $request['expenses_application_id'] = $request[self::REQUEST_APPLICATION_ID] ?? 0;
                $request['expenses_title'] = Config::get('services.OTHER_EXPENSES_TITLE')[2];
                $request['expenses_payment_reference_number'] = '';
                $request['expenses_payment_date'] = Carbon::now();
                $request['expenses_amount'] = $request['amount'] ?? 0;
                $request['expenses_remarks'] = '';
                $this->directRecruitmentExpensesServices->addOtherExpenses($request);

            }
        }elseif( isset($request['amount'])){
            if(is_null($onboardingEmbassy)){
                $this->onboardingEmbassy::create([
                    "onboarding_attestation_id" => $request['onboarding_attestation_id'] ?? 0,
                    "embassy_attestation_id" => $request['embassy_attestation_id'] ?? 0,
                    "amount" => $request['amount'] ?? 0,
                    "created_by" =>  $params['created_by'] ?? 0,
                    "modified_by" =>  $params['created_by'] ?? 0
                ]); 
            }else{
                $onboardingEmbassy->update([
                    "amount" => $request['amount'],
                    "modified_by" =>  $params['created_by'] ?? 0
                ]); 
            }
            // ADD OTHER EXPENSES - Onboarding - Attestation Costing
            $request['expenses_application_id'] = $request[self::REQUEST_APPLICATION_ID] ?? 0;
            $request['expenses_title'] = Config::get('services.OTHER_EXPENSES_TITLE')[2];
            $request['expenses_payment_reference_number'] = '';
            $request['expenses_payment_date'] = Carbon::now();
            $request['expenses_amount'] = $request['amount'] ?? 0;
            $request['expenses_remarks'] = '';
            $this->directRecruitmentExpensesServices->addOtherExpenses($request);
        }else{
            return false;
        }
        return true;
    }
    
    /**
     * Delete a embassy file
     *
     * @param array $request has the following keys 
     * - company_id : company id
     * - onboarding_embassy_id : on boarding embassy id
     * 
     * @return array information with below returns.
     * - "isDeleted": returns false, if the request data not found 
     * - "isDeleted": returns true on successful delete.
     */   
    public function deleteEmbassyFile($request): array
    {   
        $data = $this->onboardingEmbassy
        ->join('onboarding_attestation', 'onboarding_attestation.id', 'onboarding_embassy.onboarding_attestation_id')
        ->join('directrecruitment_applications', function ($join) use($request) {
            $join->on('onboarding_attestation.application_id', '=', 'directrecruitment_applications.id')
            ->where('directrecruitment_applications.company_id', $request[self::REQUEST_COMPANY_ID]);
        })->find($request['onboarding_embassy_id']); 
        if(is_null($data)){
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
     * Get User details
     *
     * @param int $referenceId 
     * 
     * @return array use details for the requested reference id
     */      
    public function getUser($referenceId)
    {   
        return User::where('reference_id',$referenceId)->where('user_type','Employee')->first('id', 'name', 'email');
    }
    
    /**
     * Update the new KSM Reference Number for the requested attestation id with following details
     * - application_id : the application is
     * - onboarding_country_id : on boarding application id
     * - id : onboarding agent id
     * - old_ksm_reference_number: existing ksm reference number to update
     * - ksm_reference_number: new ksm reference number to be updates.
     * 
     * @param array $request to update the new ksm reference number
     * - returns bool value on the udation of onboarding attestation table
     *
     */ 
    public function updateKSMReferenceNumber(array $request): bool
    {   
        return $this->onboardingAttestation->where('application_id', $request[self::REQUEST_APPLICATION_ID])
                ->where('onboarding_country_id', $request[self::REQUEST_ONBOARDING_COUNTRY_ID])
                ->where('onboarding_agent_id', $request['id'])
                ->where('ksm_reference_number', $request['old_ksm_reference_number'])
                ->update(['ksm_reference_number' => $request[self::REQUEST_KSM_REFERENCE_NUMBER]]); 
    }

}