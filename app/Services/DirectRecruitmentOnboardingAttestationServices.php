<?php

namespace App\Services;

use App\Jobs\RunnerNotificationMail;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use App\Models\OnboardingAttestation;
use App\Models\OnboardingDispatch;
use App\Models\OnboardingEmbassy;
use App\Models\EmbassyAttestationFileCosting;
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

    public const ONBOARDING_STATUS_AGENT_ADDED = 3;
    public const DEFAULT_INT_VALUE = 0;
    public const STATUS_ACTIVE = 1;
    public const STATUS_IN_ACTIVE = 0;
    public const REFERENCE_NUMBER_PREFIX = 'JO00000';
    public const MESSAGE_DELETED_NOT_FOUND = 'Data not found';
    public const MESSAGE_DELETED_SUCCESSFULLY = 'Deleted Successfully';
    public const EMBASSY_ATTESTATION_COSTING = 'Embassy Attestation Costing';

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
     * @var NotificationServices
     */
    private NotificationServices $notificationServices;

    /**
     * Constructs a new instance of the class.
     *
     * @param OnboardingAttestation $onboardingAttestation An instance of the OnboardingAttestation class.
     * @param OnboardingDispatch $onboardingDispatch An instance of the OnboardingDispatch class.
     * @param OnboardingEmbassy $onboardingEmbassy An instance of the OnboardingEmbassy class.
     * @param EmbassyAttestationFileCosting $embassyAttestationFileCosting An instance of the EmbassyAttestationFileCosting class.
     * @param DirectRecruitmentOnboardingCountryServices $directRecruitmentOnboardingCountryServices An instance of the DirectRecruitmentOnboardingCountryServices class.
     * @param Storage $storage An instance of the Storage class.
     * @param DirectRecruitmentExpensesServices $directRecruitmentExpensesServices An instance of the DirectRecruitmentExpensesServices class.
     * @param NotificationServices $notificationServices An instance of the NotificationServices class.
     * @return void
     */

    public function __construct(
        OnboardingAttestation                      $onboardingAttestation,
        OnboardingDispatch                         $onboardingDispatch,
        OnboardingEmbassy                          $onboardingEmbassy,
        EmbassyAttestationFileCosting              $embassyAttestationFileCosting,
        DirectRecruitmentOnboardingCountryServices $directRecruitmentOnboardingCountryServices,
        Storage                                    $storage,
        DirectRecruitmentExpensesServices          $directRecruitmentExpensesServices,
        NotificationServices                       $notificationServices
    )
    {
        $this->onboardingAttestation = $onboardingAttestation;
        $this->onboardingDispatch = $onboardingDispatch;
        $this->onboardingEmbassy = $onboardingEmbassy;
        $this->embassyAttestationFileCosting = $embassyAttestationFileCosting;
        $this->directRecruitmentOnboardingCountryServices = $directRecruitmentOnboardingCountryServices;
        $this->storage = $storage;
        $this->directRecruitmentExpensesServices = $directRecruitmentExpensesServices;
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
     * Returns a paginated list of onboarding attestation records.
     *
     * @param array $request The request data containing application id, onboarding country id, and company id
     * @return LengthAwarePaginator The paginated list of onboarding attestation records.
     */
    public function list($request)
    {
        return $this->onboardingAttestation
            ->join('directrecruitment_applications', function ($join) use ($request) {
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
     * Returns an onboarding attestation with direct recruitment application details.
     *
     * @param array $request The request data containing company id and attestation id
     * @return mixed Returns the onboarding attestation with direct recruitment application details.
     */
    public function show($request): mixed
    {
        return $this->onboardingAttestation->join('directrecruitment_applications', function ($join) use ($request) {
            $join->on('onboarding_attestation.application_id', '=', 'directrecruitment_applications.id')
                ->whereIn('directrecruitment_applications.company_id', $request[self::REQUEST_COMPANY_ID]);
        })
            ->where('onboarding_attestation.id', $request['id'])
            ->first('onboarding_attestation.*');
    }

    /**
     * Checks for onboarding attestation data based on the given request.
     *
     * @param mixed $request The request data containing application id, onboarding_country_id, onboarding_agent_id, ksm_reference_number.
     * @return mixed Returns the onboarding attestation data if found, otherwise returns null.
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
     * Creates a direct recruitment onboarding attestation.
     *
     * @param array $request The request data containing application_id, onboarding_country_id, onboarding_agent_id, ksm_reference_number, created_by, modified_by
     * @return void
     */
    public function createDirectRecruitmentOnboardingAttestation(array $request): void
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
     * Create a new record in the database.
     *
     * @param array $request The request data containing the necessary information to create a record.
     * @return array|bool Returns the result of the operation.
     * - true: The record was successfully created.
     * - false: The record was not created due to existing onboarding attestation data.
     * - array: An array with 'error' key containing the validation errors if the request data fails validation.
     */
    public function create($request): bool|array
    {
        $onboardingAttestation = $this->checkOnboardingAttestationData($request);

        if (is_null($onboardingAttestation)) {
            $validator = Validator::make($request, $this->createValidation());
            if ($validator->fails()) {
                return [
                    'error' => $validator->errors()
                ];
            }

            $this->createDirectRecruitmentOnboardingAttestation($request);

            return true;
        }
        return false;
    }

    /**
     * Get the onboarding attestation details.
     *
     * @param array $request The request data containing the ID of the onboarding attestation
     * @return mixed Returns details of the onboarding attestation.
     */
    public function getOnboardingAttestation(array $request)
    {
        return $this->onboardingAttestation
            ->join('directrecruitment_applications', function ($join) use ($request) {
                $join->on('onboarding_attestation.application_id', '=', 'directrecruitment_applications.id')
                    ->where('directrecruitment_applications.company_id', $request[self::REQUEST_COMPANY_ID]);
            })
            ->select('onboarding_attestation.id', 'onboarding_attestation.application_id', 'onboarding_attestation.onboarding_country_id', 'onboarding_attestation.onboarding_agent_id', 'onboarding_attestation.ksm_reference_number', 'onboarding_attestation.submission_date', 'onboarding_attestation.collection_date', 'onboarding_attestation.item_name', 'onboarding_attestation.status', 'onboarding_attestation.file_url', 'onboarding_attestation.remarks', 'onboarding_attestation.created_by', 'onboarding_attestation.modified_by', 'onboarding_attestation.created_at', 'onboarding_attestation.updated_at', 'onboarding_attestation.deleted_at')
            ->find($request['id']);
    }

    /**
     * Get the count of attestation documents collected for a specific onboarding process and country.
     *
     * @param int $applicationId The ID of the application.
     * @param int $onboardingCountryId The ID of the onboarding country.
     * @return int Returns the count of attestation documents collected.
     */
    public function getOnboardingAttestationCollectedCount($applicationId, $onboardingCountryId): int
    {
        return $this->onboardingAttestation->where('application_id', $applicationId)
            ->where('onboarding_country_id', $onboardingCountryId)
            ->where('status', 'Collected')
            ->count();
    }

    /**
     * Updates the onboarding status for a specific application and country.
     *
     * @param int $requestApplicationId The application ID.
     * @param int $requestOnboardingCountryId The onboarding country ID.
     * @return void
     */
    public function onboardingStatusUpdate(int $requestApplicationId, int $requestOnboardingCountryId): void
    {
        $onBoardingStatus['application_id'] = $requestApplicationId;
        $onBoardingStatus['country_id'] = $requestOnboardingCountryId;
        $onBoardingStatus['onboarding_status'] = self::ONBOARDING_STATUS_AGENT_ADDED; //Agent Added
        $this->directRecruitmentOnboardingCountryServices->onboarding_status_update($onBoardingStatus);
    }

    /**
     * Update the onboarding attestation record.
     *
     * @param array $request The request data containing the updated values.
     * @return bool|array Returns `true` if the update is successful. If validation fails, it returns an array containing the validation errors. If the onboarding attestation is not found
     *, it returns an array indicating the invalid user.
     */
    public function update($request): bool|array
    {
        $validator = Validator::make($request, $this->updateValidation());
        if ($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }
        $onboardingAttestation = $this->getOnboardingAttestation($request);
        if (is_null($onboardingAttestation)) {
            return [
                'InvalidUser' => true
            ];
        }
        $this->updateSubmissionAndCollectionDate($request, $onboardingAttestation);
        $this->updateOnboardingAttestationAttributes($request, $onboardingAttestation);
        $onboardingAttestation->save();
        return true;
    }

    /**
     * Update the attributes of the given on-boarding attestation.
     *
     * @param array $request The request data containing the updated attributes (file_url, remarks, status, modified_by).
     * @param object $onboardingAttestation The on-boarding attestation object to be updated.
     * @return void
     */
    private function updateOnboardingAttestationAttributes($request, $onboardingAttestation): void
    {
        $onboardingAttestation->file_url = $request['file_url'] ?? $onboardingAttestation->file_url;
        $onboardingAttestation->remarks = $request['remarks'] ?? $onboardingAttestation->remarks;
        $onboardingAttestation->status = $request['status'] ?? $onboardingAttestation->status;
        $onboardingAttestation->modified_by = $request['modified_by'] ?? $onboardingAttestation->modified_by;
    }

    /**
     * Update the submission and collection dates for the onboarding attestation.
     *
     * @param array $request The request data containing submission_date and collection_date
     * @param object $onboardingAttestation The onboarding attestation object
     *
     * @return void
     */
    private function updateSubmissionAndCollectionDate($request, $onboardingAttestation): void
    {
        if (!empty($request['submission_date'])) {
            $request['status'] = 'Submitted';
            $onboardingAttestation->submission_date = $request['submission_date'];
        }
        if (!empty($request['collection_date'])) {
            $request['status'] = 'Collected';
            $onboardingAttestation->collection_date = $request['collection_date'];
            $attestationCount = $this->getOnboardingAttestationCollectedCount($onboardingAttestation->application_id, $onboardingAttestation->onboarding_country_id);
            if ($attestationCount == 0) {
                $this->onboardingStatusUpdate($request[self::REQUEST_APPLICATION_ID], $request[self::REQUEST_ONBOARDING_COUNTRY_ID]);
            }
        }
    }

    /**
     * Get the dispatch details of direct recruitment on-boarding.
     *
     * @param array $request The request data containing company_id and onboarding_attestation_id.
     * @return mixed Returns the dispatch details of direct recruitment on-boarding.
     */
    public function showDispatch($request)
    {
        return $this->onboardingDispatch
            ->join('onboarding_attestation', 'onboarding_attestation.id', 'onboarding_dispatch.onboarding_attestation_id')
            ->join('directrecruitment_applications', function ($join) use ($request) {
                $join->on('onboarding_attestation.application_id', '=', 'directrecruitment_applications.id')
                    ->whereIn('directrecruitment_applications.company_id', $request[self::REQUEST_COMPANY_ID]);
            })->where('onboarding_dispatch.onboarding_attestation_id', $request[self::REQUEST_ONBOARDING_ATTESTATION_ID])
            ->select('onboarding_dispatch.*')
            ->get();
    }

    /**
     * Get the on-boarding attestation data.
     *
     * @param array $request The request data containing the onboarding attestation ID and company ID.
     *                      The $request['onboarding_attestation_id'] should contain the ID of the onboarding attestation.
     *                      The $request['company_id'] should contain the ID of the company.
     * @return mixed Returns the details of the on-boarding attestation data.
     *               Returns null if no data is found.
     */
    public function getOnboardingAttestationData(array $request): mixed
    {

        return $this->onboardingAttestation->join('directrecruitment_applications', function ($join) use ($request) {
            $join->on('onboarding_attestation.application_id', '=', 'directrecruitment_applications.id')
                ->where('directrecruitment_applications.company_id', $request[self::REQUEST_COMPANY_ID]);
        })
            ->where('onboarding_attestation.id', $request[self::REQUEST_ONBOARDING_ATTESTATION_ID])
            ->select('onboarding_attestation.id', 'onboarding_attestation.application_id', 'onboarding_attestation.onboarding_country_id', 'onboarding_attestation.onboarding_agent_id', 'onboarding_attestation.ksm_reference_number', 'onboarding_attestation.submission_date', 'onboarding_attestation.collection_date', 'onboarding_attestation.item_name', 'onboarding_attestation.status', 'onboarding_attestation.file_url', 'onboarding_attestation.remarks', 'onboarding_attestation.created_by', 'onboarding_attestation.modified_by', 'onboarding_attestation.created_at', 'onboarding_attestation.updated_at', 'onboarding_attestation.deleted_at')->first();
    }

    /**
     * Create a new on-boarding dispatch record.
     *
     * @param array $request The request data containing onboarding_attestation_id, date, time, reference_number,
     * employee_id, from, calltime, area, employer_name, phone_number, remarks, created_by, and modified_by.
     * @return void
     */
    public function onBoardingDispatchCreate(array $request): void
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
            'created_by' => $request['created_by'] ?? 0,
            'modified_by' => $request['created_by'] ?? 0
        ]);
    }

    /**
     * Update the on-boarding dispatch details.
     *
     * @param object $onboardingDispatch The on-boarding dispatch object to be updated.
     * @param array $request The request data containing the updated details.
     * @return void
     */
    public function onBoardingDispatchUpdate($onboardingDispatch, array $request): void
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
            'modified_by' => $request['created_by'] ?? 0
        ]);
    }

    /**
     * Create a dispatch notification.
     *
     * @param object $getUser The user object.
     * @param array $request The request data containing user and notification information.
     * @return void
     */
    public function createDispatchNotification($getUser, array $request): void
    {
        $NotificationParams = [
            'user_id' => $request['employee_id'],
            'from_user_id' => $request['created_by'],
            'type' => 'Dispatches',
            'title' => 'Dispatches',
            'message' => $request['reference_number'] . ' Dispatch is Assigned',
            'status' => self::STATUS_ACTIVE,
            'read_flag' => self::STATUS_IN_ACTIVE,
            'created_by' => $request['created_by'],
            'modified_by' => $request['created_by'],
            'company_id' => $request[self::REQUEST_COMPANY_ID],
        ];

        $this->notificationServices->insertDispatchNotification($NotificationParams);

        $DatabaseName = Config::get('database.connections.mysql.database');
        $QueueServiceRunnerMail = Config::get('services.RUNNER_NOTIFICATION_MAIL');
        $QueueServiceConnection = Config::get('services.QUEUE_CONNECTION');

        dispatch(new RunnerNotificationMail($DatabaseName, $getUser, $NotificationParams['message']))
            ->onQueue($QueueServiceRunnerMail)
            ->onConnection($QueueServiceConnection);
    }

    /**
     * Update a dispatch details
     *
     * @param array $request The data used to update the dispatch.
     * The array should contain the following keys:
     * - company_id: Direct Recruitment application company_id.
     * - onboarding_attestation_id: on boarding attestation id
     * - date: Dispatch date
     * - time: Dispatch time
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
     * - "true": A boolean returns true if attestation updated successfully
     *
     */
    public function updateDispatch($request): bool|array
    {

        $attestationCheck = $this->getOnboardingAttestationData($request);
        if (is_null($attestationCheck)) {
            return [
                'InvalidUser' => true
            ];
        }
        $onboardingDispatch = $this->onboardingDispatch->where(
            'onboarding_attestation_id', $request['onboarding_attestation_id']
        )->first(['id', 'onboarding_attestation_id', 'date', 'time', 'reference_number', 'employee_id', 'from', 'calltime', 'area', 'employer_name', 'phone_number', 'remarks']);

        $request['reference_number'] = self::REFERENCE_NUMBER_PREFIX . $this->onboardingDispatch->count() + 1;

        $validator = Validator::make($request, $this->updateDispatchValidation());
        if ($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }

        if (is_null($onboardingDispatch)) {
            $this->onBoardingDispatchCreate($request);
        } else {
            $this->onBoardingDispatchUpdate($onboardingDispatch, $request);

        }

        $getUser = $this->getUser($request['employee_id']);
        if ($getUser) {
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
            ->join('directrecruitment_applications', function ($join) use ($request) {
                $join->on('onboarding_attestation.application_id', '=', 'directrecruitment_applications.id')
                    ->whereIn('directrecruitment_applications.company_id', $request[self::REQUEST_COMPANY_ID]);
            })
            ->where('onboarding_attestation.id', $request[self::REQUEST_ONBOARDING_ATTESTATION_ID])
            ->select('directrecruitment_onboarding_countries.country_id')
            ->distinct('directrecruitment_onboarding_countries.country_id')
            ->first();

        if (is_null($onboardingAttestation)) {
            return [
                'InvalidUser' => true
            ];
        }
        $request['country_id'] = $onboardingAttestation['country_id'] ?? self::DEFAULT_INT_VALUE;

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
            ->orderBy('embassy_attestation_file_costing.id', 'DESC')
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
        $attestationCheck = $this->getOnboardingAttestationData($request);
        if (is_null($attestationCheck)) {
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
     * Get the direct recruitment on boarding attestation application.
     *
     * @param array $params The request data containing company_id, onboarding_attestation_id
     * @return mixed Returns details of direct recruitment on-boarding attestation application.
     */
    public function onboardingAttestationApplication(array $params): mixed
    {
        return $this->onboardingAttestation
            ->join('directrecruitment_applications', function ($join) use ($params) {
                $join->on('onboarding_attestation.application_id', '=', 'directrecruitment_applications.id')
                    ->where('directrecruitment_applications.company_id', $params[self::REQUEST_COMPANY_ID]);
            })
            ->where('onboarding_attestation.id', $params[self::REQUEST_ONBOARDING_ATTESTATION_ID])
            ->first(['onboarding_attestation.application_id']);
    }

    /**
     * Fetches the details of the onboarding embassy record based on the provided request.
     *
     * @param array $request An associative array containing the following keys:
     *   - onboarding_attestation_id (integer): The ID of the onboarding attestation
     *   - embassy_attestation_id (integer): The ID of the embassy attestation
     *
     * @return mixed|null The onboarding embassy details as an array, or null if not found.
     */
    public function onboardingEmbassyDetails(array $request): mixed
    {
        return $this->onboardingEmbassy->where([
            ['onboarding_attestation_id', $request[self::REQUEST_ONBOARDING_ATTESTATION_ID]],
            ['embassy_attestation_id', $request['embassy_attestation_id']],
            ['deleted_at', null],
        ])->first(['id', 'onboarding_attestation_id', 'embassy_attestation_id', 'file_name', 'file_type', 'file_url', 'amount']);
    }

    /**
     * Upload multiple files for Embassy Attachment.
     * Add | Update File_name, file_type, file_url, amount data in on Boarding Embassy from the given request
     * Insert new record in expense table for Onboarding - Attestation Costing
     *
     * @param array $files A array of files to be uploaded.
     * @param array $request A array consists of.
     * - onboarding_attestation_id: The onboarding gestation id
     * - embassy_attestation_id: The embassy attestation id
     * - file_name: Uploaded file name
     * - file_type: uploaded file type
     * - file_url: uploaded file url
     * - amount: The amount value
     * - created_by: The user who created the onboarding Embassy with amount information
     * - modified_by: The user who modified the onboarding Embassy with amount information
     * @param object $onboardingEmbassy The object for onboarding Embassy to add | Update the details.
     *
     * @return void
     *
     */
    public function uploadFiles($files, array $request, $onboardingEmbassy): void
    {
        foreach ($files as $file) {
            $fileName = $file->getClientOriginalName();
            $filePath = '/directRecruitment/onboarding/embassyAttestationCosting/' . $fileName;

            $linode = $this->storage::disk('linode');
            $linode->put($filePath, file_get_contents($file));

            $fileUrl = $linode->url($filePath);

            if (is_null($onboardingEmbassy)) {
                $this->onboardingEmbassy::create([
                    "onboarding_attestation_id" => $request['onboarding_attestation_id'] ?? self::DEFAULT_INT_VALUE,
                    "embassy_attestation_id" => $request['embassy_attestation_id'] ?? self::DEFAULT_INT_VALUE,
                    "file_name" => $fileName,
                    "file_type" => self::EMBASSY_ATTESTATION_COSTING,
                    "file_url" => $fileUrl,
                    "amount" => $request['amount'] ?? self::DEFAULT_INT_VALUE,
                    "created_by" => $request['created_by'] ?? self::DEFAULT_INT_VALUE,
                    "modified_by" => $request['created_by'] ?? self::DEFAULT_INT_VALUE
                ]);
            } else {
                $onboardingEmbassy->update([
                    "file_name" => $fileName,
                    "file_url" => $fileUrl,
                    "amount" => $request['amount'] ?? $onboardingEmbassy->amount,
                    "modified_by" => $request['created_by'] ?? self::DEFAULT_INT_VALUE
                ]);
            }

            // ADD OTHER EXPENSES - Onboarding - Attestation Costing
            $this->addExpenses($request);
        }
    }

    /**
     * Add | Update Amount data in onboarding Embassy from the given request
     * Insert new record in expense table for Onboarding - Attestation Costing
     *
     * @param array $request A array consists of.
     * - onboarding_attestation_id: The onboarding attestation id
     * - embassy_attestation_id: The embassy attestation id
     * - amount: The amount value
     * - created_by: The user who created the onboarding Embassy with amount information
     * - modified_by: The user who modified the onboarding Embassy with amount information
     * @param object $onboardingEmbassy The object for onboarding Embassy to add | Update the details.
     *
     * @return void
     *
     */
    public function updateAmountData(array $request, $onboardingEmbassy): void
    {
        if (is_null($onboardingEmbassy)) {
            $this->onboardingEmbassy::create([
                "onboarding_attestation_id" => $request['onboarding_attestation_id'] ?? self::DEFAULT_INT_VALUE,
                "embassy_attestation_id" => $request['embassy_attestation_id'] ?? self::DEFAULT_INT_VALUE,
                "amount" => $request['amount'] ?? self::DEFAULT_INT_VALUE,
                "created_by" => $request['created_by'] ?? self::DEFAULT_INT_VALUE,
                "modified_by" => $request['created_by'] ?? self::DEFAULT_INT_VALUE
            ]);
        } else {
            $onboardingEmbassy->update([
                "amount" => $request['amount'],
                "modified_by" => $request['created_by'] ?? self::DEFAULT_INT_VALUE
            ]);
        }
        // ADD OTHER EXPENSES - Onboarding - Attestation Costing
        $this->addExpenses($request);

    }

    /**
     * Add the Expenses details for OTHER EXPENSES - Onboarding - Attestation Costing.
     *
     * @param array $request
     * @return void Inserted the new expense for OTHER EXPENSES - Onboarding - Attestation Costing.
     */
    public function addExpenses(array $request): void
    {
        $request['expenses_application_id'] = $request[self::REQUEST_APPLICATION_ID] ?? self::DEFAULT_INT_VALUE;
        $request['expenses_title'] = Config::get('services.OTHER_EXPENSES_TITLE')[2];
        $request['expenses_payment_reference_number'] = '';
        $request['expenses_payment_date'] = Carbon::now();
        $request['expenses_amount'] = $request['amount'] ?? self::DEFAULT_INT_VALUE;
        $request['expenses_remarks'] = '';
        $this->directRecruitmentExpensesServices->addOtherExpenses($request);
    }

    /**
     * Uploads a embassy file
     *
     * @param array $request has the following keys
     * The request data containing company id, onboarding_attestation_id, country_id
     * embassy_attestation_id
     *
     * @return bool|array show embassy attestation file costing with onboarding embassy details.
     * - "validate": An array of validation errors, if any.
     * - "InvalidUser": A boolean returns true if user is invalid.
     */
    public function uploadEmbassyFile($request): bool|array
    {
        $params = $request->all();
        $user = JWTAuth::parseToken()->authenticate();
        $params['created_by'] = $user['id'];
        $params['company_id'] = $user[self::REQUEST_COMPANY_ID];

        $validator = Validator::make($request->toArray(), $this->uploadEmbassyFileValidation());
        if ($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }

        $onboardingAttestation = $this->onboardingAttestationApplication($params);
        if (is_null($onboardingAttestation)) {
            return [
                'InvalidUser' => true
            ];
        }

        $params['application_id'] = $onboardingAttestation['application_id'] ?? 0;

        $onboardingEmbassy = $this->onboardingEmbassyDetails($params);

        if ($request->hasFile('attachment')) {
            $this->uploadFiles($request->file('attachment'), $params, $onboardingEmbassy);
        } elseif (isset($request['amount'])) {
            $this->updateAmountData($params, $onboardingEmbassy);
        } else {
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
            ->join('directrecruitment_applications', function ($join) use ($request) {
                $join->on('onboarding_attestation.application_id', '=', 'directrecruitment_applications.id')
                    ->where('directrecruitment_applications.company_id', $request[self::REQUEST_COMPANY_ID]);
            })->select('onboarding_embassy.id', 'onboarding_embassy.onboarding_attestation_id', 'onboarding_embassy.embassy_attestation_id', 'onboarding_embassy.file_name', 'onboarding_embassy.file_type', 'onboarding_embassy.file_url', 'onboarding_embassy.amount', 'onboarding_embassy.created_by', 'onboarding_embassy.modified_by', 'onboarding_embassy.created_at', 'onboarding_embassy.updated_at', 'onboarding_embassy.deleted_at')->find($request['onboarding_embassy_id']);
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
     * Get User details
     *
     * @param int $referenceId
     *
     * @return array use details for the requested reference id
     */
    public function getUser($referenceId)
    {
        return User::where('reference_id', $referenceId)->where('user_type', 'Employee')->first('id', 'name', 'email');
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
