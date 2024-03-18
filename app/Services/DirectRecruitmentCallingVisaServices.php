<?php

namespace App\Services;

use App\Models\DirectRecruitmentCallingVisaStatus;
use App\Models\Workers;
use App\Models\WorkerVisa;
use App\Models\CancellationAttachment;
use App\Models\DirectRecruitmentOnboardingCountry;
use Closure;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Events\WorkerQuotaUpdated;
use App\Events\KSMQuotaUpdated;

class DirectRecruitmentCallingVisaServices
{
    /**
     * @var DirectRecruitmentCallingVisaStatus
     */
    private DirectRecruitmentCallingVisaStatus $directRecruitmentCallingVisaStatus;
    /**
     * @var Workers
     */
    private Workers $workers;
    /**
     * @var WorkerVisa
     */
    private WorkerVisa $workerVisa;
    /**
     * @var CancellationAttachment
     */
    private CancellationAttachment $cancellationAttachment;
    /**
     * @var Storage
     */
    private Storage $storage;
    /**
     * @var DirectRecruitmentOnboardingCountry
     */
    private DirectRecruitmentOnboardingCountry $directRecruitmentOnboardingCountry;

    /**
     * DirectRecruitmentCallingVisaServices constructor.
     * @param DirectRecruitmentCallingVisaStatus $directRecruitmentCallingVisaStatus
     * @param Workers $workers
     * @param WorkerVisa $workerVisa
     * @param CancellationAttachment $cancellationAttachment
     * @param Storage $storage
     * @param DirectRecruitmentOnboardingCountry $directRecruitmentOnboardingCountry
     */
    public function __construct(
        DirectRecruitmentCallingVisaStatus $directRecruitmentCallingVisaStatus,
        Workers                            $workers,
        WorkerVisa                         $workerVisa,
        CancellationAttachment             $cancellationAttachment,
        Storage                            $storage,
        DirectRecruitmentOnboardingCountry $directRecruitmentOnboardingCountry
    )
    {
        $this->directRecruitmentCallingVisaStatus = $directRecruitmentCallingVisaStatus;
        $this->workers = $workers;
        $this->workerVisa = $workerVisa;
        $this->cancellationAttachment = $cancellationAttachment;
        $this->storage = $storage;
        $this->directRecruitmentOnboardingCountry = $directRecruitmentOnboardingCountry;

    }

    /**
     * @return array
     */
    public function createValidation(): array
    {
        return [
            'calling_visa_reference_number' => 'required|regex:/^[a-zA-Z0-9\/]*$/',
            'submitted_on' => 'required|date|date_format:Y-m-d|before:tomorrow'
        ];
    }

    /**
     * @return array
     */
    public function searchValidation(): array
    {
        return [
            'search' => 'required|min:3'
        ];
    }

    /**
     * @return array
     */
    public function cancelValidation(): array
    {
        return [
            'workers' => 'required'
        ];
    }

    /**
     * Call the visa status list for direct recruitment applications.
     *
     * @param array $request The request data.
     *   - string $request['company_id'] The IDs of the companies.
     *   - int $request['application_id'] The ID of the application.
     *   - int $request['onboarding_country_id'] The ID of the onboarding country.
     *
     * @return LengthAwarePaginator The paginated list of visa status.
     */
    public function callingVisaStatusList($request): mixed
    {
        return $this->directRecruitmentCallingVisaStatus
            ->join('directrecruitment_applications', function ($join) use ($request) {
                $join->on('direct_recruitment_calling_visa_status.application_id', '=', 'directrecruitment_applications.id')
                    ->whereIn('directrecruitment_applications.company_id', $request['company_id']);
            })
            ->select('direct_recruitment_calling_visa_status.id', 'direct_recruitment_calling_visa_status.item', 'direct_recruitment_calling_visa_status.updated_on', 'direct_recruitment_calling_visa_status.status')
            ->where([
                'direct_recruitment_calling_visa_status.application_id' => $request['application_id'],
                'direct_recruitment_calling_visa_status.onboarding_country_id' => $request['onboarding_country_id']
            ])
            ->orderBy('direct_recruitment_calling_visa_status.id', 'desc')
            ->paginate(Config::get('services.paginate_row'));
    }

    /**
     * Submits calling visa for a group of workers.
     *
     * @param array $request The request data containing the following keys:
     *                      - workers: An array of worker IDs.
     *                      - company_id: The ID of the company.
     *                      - onboarding_country_id: The ID of the onboarding country.
     *                      - application_id: The ID of the application.
     *                      - calling_visa_reference_number: The reference number of the calling visa.
     *                      - submitted_on: The date of submission.
     *                      - modified_by: The ID of the user who modified the calling visa.
     * @return array|bool Returns true if the calling visa is successfully submitted, false otherwise.
     */
    public function submitCallingVisa(array $request)
    {
        ['workers' => $workers, 'company_id' => $companyId, 'onboarding_country_id' => $onboardingCountryId, 'application_id' => $applicationId, 'calling_visa_reference_number' => $callingVisaReferenceNumber, 'submitted_on' => $submittedOn, 'modified_by' => $modifiedBy] = $request;

        $validation = $this->validateRequest($request);

        if (isset($validation['error'])) {
            return $validation;
        }

        $validation = $this->verifyWorkersCompany($workers, $companyId);

        if (isset($validation['InvalidUser'])) {
            return $validation;
        }

        $validation = $this->validateApplication($companyId, $onboardingCountryId, $applicationId);

        if (isset($validation['InvalidUser'])) {
            return $validation;
        }

        $validation = $this->checkCallingVisaWorkerCount($callingVisaReferenceNumber, $workers);

        if (isset($validation['workerCountError'])) {
            return $validation;
        }

        $this->workerVisa->whereIn('worker_id', $workers)->update([
            'calling_visa_reference_number' => $callingVisaReferenceNumber,
            'submitted_on' => $submittedOn,
            'status' => 'Processed',
            'modified_by' => $modifiedBy
        ]);

        $this->updateCallingVisaStatus($applicationId, $onboardingCountryId, $modifiedBy);

        return true;
    }

    /**
     * Validate the given request.
     *
     * @param array $request [The request data to be validated]
     *
     * @return array | void  - An array with the error messages if validation fails,
     *               otherwise, it returns nothing
     */
    private function validateRequest(array $request)
    {
        $validator = Validator::make($request, $this->createValidation());
        if ($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }
    }

    /**
     * Validate the given request for search.
     *
     * @param array $request [The request data to be validated]
     *
     * @return array | void  - An array with the error messages if validation fails,
     *               otherwise, it returns nothing
     */
    private function validateSearchRequest(array $request)
    {
        $validator = Validator::make($request, $this->searchValidation());
        if ($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }
    }

    /**
     * Verify if all given workers belong to the specified company.
     *
     * @param array $workers The list of worker IDs
     * @param int $companyId The ID of the company
     *
     * @return array|null|void Returns an array with an 'InvalidUser' key set to true if not all workers belong to the specified company,
     *                   otherwise returns null.
     */
    private function verifyWorkersCompany(array $workers, int $companyId)
    {
        $workerCompanyCount = $this->workers->whereIn('id', $workers)->where('company_id', $companyId)->count();
        if ($workerCompanyCount != count($workers)) {
            return [
                'InvalidUser' => true
            ];
        }
    }

    /**
     * Validate the application based on the provided parameters.
     *
     * @param int $companyId The ID of the company.
     * @param int $onboardingCountryId The ID of the onboarding country.
     * @param int $applicationId The ID of the application.
     *
     * @return array|null|void - If the application is invalid, returns an array with 'InvalidUser' set to true. Otherwise, returns null.
     */
    private function validateApplication(int $companyId, int $onboardingCountryId, int $applicationId)
    {
        $applicationCheck = $this->checkForApplication($companyId, $onboardingCountryId);
        if (is_null($applicationCheck) || ($applicationCheck->application_id != $applicationId)) {
            return [
                'InvalidUser' => true
            ];
        }
    }

    /**
     * Check the total worker count against the limit set in the configuration for calling visas.
     * If the total count exceeds the limit, return an error message.
     *
     * @param string $callingVisaReferenceNumber The calling visa reference number.
     * @param array $workers The array of workers to be added to the count.
     * @return array|null|void Returns an array with 'workerCountError' set to true if total count exceeds the limit,
     *                     otherwise returns null.
     */
    private function checkCallingVisaWorkerCount(string $callingVisaReferenceNumber, array $workers)
    {
        if (!empty($workers) && !empty($callingVisaReferenceNumber)) {
            $workerCount = $this->workerVisa->where('calling_visa_reference_number', $callingVisaReferenceNumber)->count('worker_id');
            $workerCount += count($workers);
            if ($workerCount > Config::get('services.CALLING_VISA_WORKER_COUNT')) {
                return [
                    'workerCountError' => true
                ];
            }
        }
    }

    /**
     *
     */
    private function updateCallingVisaStatus(int $applicationId, int $onboardingCountryId, string $modifiedBy): void
    {
        $this->directRecruitmentCallingVisaStatus->where([
            'application_id' => $applicationId,
            'onboarding_country_id' => $onboardingCountryId
        ])->update(['updated_on' => Carbon::now(), 'modified_by' => $modifiedBy]);
    }

    /**
     * Retrieve a list of workers based on the given request parameters.
     *
     * @param array $request The request parameters containing the following keys:
     *                      - search (optional): The search keyword to filter workers by name, visa number, or passport number.
     *                      - company_id: The company IDs to filter workers by.
     *                      - user: The user information containing the user type and reference ID.
     *                      - application_id: The application ID to filter workers by.
     *                      - onboarding_country_id: The onboarding country ID to filter workers by.
     *                      - agent_id (optional): The agent ID to filter workers by.
     *                      - export (optional): Flag indicating if the data will be exported.
     *
     * @return Collection|Paginator|array  The list of workers or an error message in case of validation failure.
     */
    public function workersList($request)
    {
        if (!empty($request['search'])) {
            $validator = Validator::make($request, $this->searchValidation());
            if ($validator->fails()) {
                return [
                    'error' => $validator->errors()
                ];
            }
        }
        $data = $this->workers
            ->leftJoin('worker_bio_medical', 'worker_bio_medical.worker_id', 'workers.id')
            ->leftJoin('worker_visa', 'worker_visa.worker_id', 'workers.id')
            ->leftjoin('directrecruitment_workers', 'directrecruitment_workers.worker_id', '=', 'workers.id')
            ->whereIn('workers.company_id', $request['company_id'])
            ->where(function ($query) use ($request) {
                if ($request['user']['user_type'] == 'Customer') {
                    $query->where('workers.crm_prospect_id', '=', $request['user']['reference_id']);
                }
            })
            ->where([
                'directrecruitment_workers.application_id' => $request['application_id'],
                'directrecruitment_workers.onboarding_country_id' => $request['onboarding_country_id']
            ])
            ->whereIn('worker_visa.status', ['Pending', 'Expired'])
            ->where(function ($query) use ($request) {
                if (!empty($request['search'])) {
                    $query->where('workers.name', 'like', '%' . $request['search'] . '%')
                        ->orWhere('worker_visa.ksm_reference_number', 'like', '%' . $request['search'] . '%')
                        ->orWhere('workers.passport_number', 'like', '%' . $request['search'] . '%');
                }
            })
            ->where(function ($query) use ($request) {
                if (!empty($request['agent_id'])) {
                    $query->where('directrecruitment_workers.agent_id', $request['agent_id']);
                }
            });

        if (!empty($request['export'])) {
            $data = $data->select('workers.id','workers.name', 'worker_visa.ksm_reference_number', 'workers.passport_number', 'worker_bio_medical.bio_medical_valid_until', 'worker_visa.status')->distinct('workers.id')
                ->orderBy('workers.id', 'desc')
                ->get();
        } else {
            $data = $data->select('workers.id', 'workers.name', 'worker_visa.ksm_reference_number', 'workers.passport_number', 'worker_bio_medical.bio_medical_valid_until', 'directrecruitment_workers.application_id', 'directrecruitment_workers.onboarding_country_id', 'directrecruitment_workers.agent_id', 'worker_visa.calling_visa_reference_number', 'worker_visa.submitted_on', 'worker_visa.status')->distinct('workers.id')
                ->orderBy('workers.id', 'desc')
                ->paginate(Config::get('services.paginate_worker_row'));
        }
        return $data;

    }

    /**
     * Show worker information with related bio-medical and visa data.
     *
     * @param array $request
     *     The request input containing 'worker_id' and 'company_id' keys.
     *
     * @return Collection
     *     A collection of worker information including related bio-medical and visa data.
     */
    public function show($request)
    {
        return $this->workers->with(['workerBioMedical' => function ($query) {
            $query->select(['id', 'worker_id', 'bio_medical_valid_until']);
        }])->with(['workerVisa' => function ($query) {
            $query->select(['id', 'worker_id', 'ksm_reference_number', 'calling_visa_reference_number', 'submitted_on', 'status']);
        }])->where('workers.id', $request['worker_id'])
            ->whereIn('company_id', $request['company_id'])
            ->select('id', 'name', 'passport_number')
            ->get();
    }

    /**
     * Cancels a worker.
     *
     * @param array $request The request data containing the worker information.
     *                      The array should have the following keys:
     *                      - workers (array): An array of worker IDs to be canceled.
     *                      - status (bool): The cancellation status.
     * @return bool|array Returns true if the worker is successfully canceled.
     *                   If there is an error, it returns an array with an error message.
     * @throws Exception If there is an unexpected error during the cancellation process.
     */
    public function cancelWorker($request): bool|array
    {
        ['workers' => $workerIds, 'status' => $status] = $this->requestValidation($request);

        if (!is_bool($status)) {
            return $status;
        }

        $status = $this->processCancellation($request, $workerIds);

        if ($status !== true) {
            return $status;
        }

        $this->directRecruitmentCallingVisaStatus->where([
            'application_id' => $request['application_id'],
            'onboarding_country_id' => $request['onboarding_country_id']
        ])->update(['updated_on' => Carbon::now(), 'modified_by' => $request['modified_by']]);

        return true;
    }

    /**
     * Validate the given request.
     *
     * @param $request - The request object to be validated.
     *
     * @return array The validated workers and status.
     */
    private function requestValidation($request): array
    {

        $validator = Validator::make($request->toArray(), $this->cancelValidation());
        $workers = $request['workers'];

        if ($validator->fails()) {
            return ['workers' => $workers, 'status' => ['error' => $validator->errors()]];
        }
        if (!empty($workers)) {
            $workers = explode(',', $workers);
            $workerCompanyCount = $this->workers->whereIn('id', $workers)
                ->where('company_id', $request['company_id'])
                ->count();
            if ($workerCompanyCount != count($workers)) {
                return ['workers' => $workers, 'status' => ['InvalidUser' => true]];
            }
        }

        return ['workers' => $workers, 'status' => true];
    }

    /**
     * Process cancellation for the given request and worker IDs.
     *
     * @param array $request The request data.
     * @param array $workerIds The worker IDs to cancel.
     * @return bool|array Returns true if cancellation is successful or an array with 'InvalidUser' key set to true if the user is invalid.
     */
    private function processCancellation($request, $workerIds): bool|array
    {
        $applicationCheck = $this->checkForApplication($request['company_id'], $request['onboarding_country_id']);
        if (is_null($applicationCheck) || $applicationCheck->application_id != $request['application_id']) {
            return ['InvalidUser' => true];
        }

        $this->workers->whereIn('id', $workerIds)->update([
            'directrecruitment_status' => 'Cancelled',
            'cancel_status' => 1,
            'remarks' => $request['remarks'] ?? '',
            'modified_by' => $request['modified_by']
        ]);

        $this->attachFilesAndUpdateQuotas($request, $workerIds);

        return true;
    }

    /**
     * Attaches files and updates quotas based on the given request and worker IDs.
     *
     * @param mixed $request The request data containing the attachment.
     * @param array $workerIds The array of worker IDs.
     * @return void
     */
    private function attachFilesAndUpdateQuotas($request, $workerIds): void
    {

        if (request()->hasFile('attachment')) {
            $this->uploadFiles($request, $workerIds);
        }

        $this->updateUtilisedQuotaBasedOnKsmReferenceNumber($request, $workerIds);

        event(new WorkerQuotaUpdated($request['onboarding_country_id'], count($workerIds), 'decrement'));
    }

    /**
     * Upload files for cancellation letters.
     *
     * @param Request $request The request object containing the uploaded files and other data.
     * @param array $workerIds An array of worker IDs to associate the uploaded files with.
     * @return void
     */
    private function uploadFiles($request, $workerIds): void
    {
        foreach ($workerIds as $workerId) {
            foreach ($request->file('attachment') as $file) {
                $fileName = $file->getClientOriginalName();
                $filePath = 'directRecruitment/workers/cancellation/' . $workerId . '/' . $fileName;
                $linode = $this->storage::disk('linode');
                $linode->put($filePath, file_get_contents($file));
                $fileUrl = $this->storage::disk('linode')->url($filePath);
                $this->cancellationAttachment->create([
                    'file_id' => $workerId,
                    'file_name' => $fileName,
                    'file_type' => 'Cancellation Letter',
                    'file_url' => $fileUrl,
                    'created_by' => $request['modified_by'],
                    'modified_by' => $request['modified_by']
                ]);
            }
        }
    }

    /**
     * Update the utilised quota based on KSM reference number.
     *
     * @param array $request The request data.
     * @param array $workerIds The array of worker IDs.
     * @return void
     */
    private function updateUtilisedQuotaBasedOnKsmReferenceNumber($request, $workerIds): void
    {
        $workerDetails = [];
        foreach ($workerIds as $worker) {
            $ksmDetails = $this->workerVisa->where('worker_id', $worker)->first(['ksm_reference_number']);
            $workerDetails[$worker] = $ksmDetails->ksm_reference_number;
        }

        $ksmCount = array_count_values($workerDetails);

        foreach ($ksmCount as $key => $value) {
            event(new KSMQuotaUpdated($request['onboarding_country_id'], $key, $value, 'decrement'));
        }
    }

    /**
     * Get the list of workers for cancellation based on the given request.
     *
     * @param array $request The request parameters.
     *                      - search: The search term for filtering workers list (optional).
     *
     * @return \Illuminate\Support\Collection|LengthAwarePaginator The list of workers for cancellation,
     *          otherwise return array contains validation errors
     */
    public function workerListForCancellation($request)
    {
        if (!empty($request['search'])) {
            $validator = $this->validateSearchRequest($request);
            if(!empty($validator['error'])) {
                return [
                    'error' => $validator['error']
                ];
            }
        }
        $data = $this->getQueryForWorkers($request);
        return $this->applyPaginationOrGet($request, $data);
    }

    /**
     * Generates the query for fetching workers based on the given request parameters.
     *
     * @param array $request The request parameters.
     * @return Builder The query builder instance.
     */
    private function getQueryForWorkers($request)
    {
        return $this->workers
            ->leftJoin('worker_visa', 'worker_visa.worker_id', 'workers.id')
            ->leftjoin('directrecruitment_workers', 'directrecruitment_workers.worker_id', '=', 'workers.id')
            ->whereIn('workers.company_id', $request['company_id'])
            ->where($this->getCustomerCondition($request))
            ->where($this->getRecruitmentCondition($request))
            ->whereNull('workers.replace_worker_id')
            ->where($this->getSearchConditions($request))
            ->where($this->getVisaReferenceCondition($request));
    }

    /**
     * Retrieves the condition to filter customers based on user type.
     *
     * @param array $request The request data containing user information.
     * @return Closure The condition to be applied to the database query.
     */
    private function getCustomerCondition($request)
    {
        return function ($query) use ($request) {
            if ($request['user']['user_type'] == 'Customer') {
                $query->where('workers.crm_prospect_id', '=', $request['user']['reference_id']);
            }
        };
    }

    /**
     * Get the recruitment condition for direct recruitment workers.
     *
     * @param array $request The request data.
     *     - application_id (int) The ID of the application.
     *     - onboarding_country_id (int) The ID of the onboarding country.
     * @return array The recruitment condition.
     */
    private function getRecruitmentCondition($request)
    {
        return [
            'directrecruitment_workers.application_id' => $request['application_id'],
            'directrecruitment_workers.onboarding_country_id' => $request['onboarding_country_id']
        ];
    }

    /**
     * Get the search conditions for the query.
     *
     * @param array $request The request data to filter the search.
     * @return Closure
     */
    private function getSearchConditions($request)
    {
        return function ($query) use ($request) {
            if (!empty($request['search'])) {
                $query->where('workers.name', 'like', '%' . $request['search'] . '%')
                    ->orWhere('worker_visa.ksm_reference_number', 'like', '%' . $request['search'] . '%')
                    ->orWhere('workers.passport_number', 'like', '%' . $request['search'] . '%');
            }
        };
    }

    /**
     * Returns a closure that can be used as a condition for querying visa references.
     *
     * @param array $request The request data.
     *
     * @return closure A closure that applies the condition for querying visa references.
     */
    private function getVisaReferenceCondition($request)
    {
        return function ($query) use ($request) {
            if (!empty($request['calling_visa_reference_number'])) {
                $query->where('worker_visa.calling_visa_reference_number', $request['calling_visa_reference_number']);
            }
        };
    }

    /**
     * Apply pagination or get data based on the request.
     *
     * @param $request - The request data.
     * @param $data - The data to be paginated.
     * @return \Illuminate\Support\Collection|LengthAwarePaginator The paginated data or the exported data.
     */
    private function applyPaginationOrGet($request, $data)
    {
        if (!empty($request['export'])) {
            return $this->getExportData($data);
        } else {
            return $this->getPagedData($data);
        }
    }


    /**
     * Get the export data from the given query.
     *
     * @param $query - The query used to fetch the export data.
     * @return \Illuminate\Support\Collection The exported data.
     */
    private function getExportData($query)
    {
        return $query->select('workers.name', 'worker_visa.ksm_reference_number', 'workers.passport_number', 'worker_visa.calling_visa_reference_number', 'worker_visa.calling_visa_valid_until', 'workers.cancel_status')
            ->distinct('workers.id')
            ->orderBy('workers.id', 'desc')
            ->get();
    }

    /**
     * Get paged data from the given query.
     *
     * @param Builder $query The query to retrieve paged data from.
     *
     * @return LengthAwarePaginator The length aware paginator containing the paged data.
     */
    private function getPagedData($query)
    {
        return $query->select('workers.id', 'workers.name', 'worker_visa.ksm_reference_number', 'workers.passport_number', 'directrecruitment_workers.application_id', 'directrecruitment_workers.onboarding_country_id', 'directrecruitment_workers.agent_id', 'worker_visa.calling_visa_reference_number', 'worker_visa.calling_visa_valid_until', 'workers.cancel_status')
            ->selectRaw("(CASE WHEN workers.cancel_status = 1 THEN 'Cancelled' ELSE '' END) AS status")
            ->distinct('workers.id')
            ->orderBy('workers.id', 'desc')
            ->paginate(Config::get('services.paginate_worker_row'));
    }

    /**
     * Check if there is an application for a specific company and onboarding country.
     *
     * @param int $companyId The ID of the company
     * @param int $onboardingCountryId The ID of the onboarding country
     * @return mixed Returns the queried onboarding country model instance or null if no application is found
     */
    private function checkForApplication(int $companyId, int $onboardingCountryId): mixed
    {
        return $this->directRecruitmentOnboardingCountry
            ->join('directrecruitment_applications', function ($join) use ($companyId) {
                $join->on('directrecruitment_onboarding_countries.application_id', '=', 'directrecruitment_applications.id')
                    ->where('directrecruitment_applications.company_id', $companyId);
            })->select('directrecruitment_onboarding_countries.id', 'directrecruitment_onboarding_countries.application_id', 'directrecruitment_onboarding_countries.country_id', 'directrecruitment_onboarding_countries.quota', 'directrecruitment_onboarding_countries.utilised_quota', 'directrecruitment_onboarding_countries.status', 'directrecruitment_onboarding_countries.onboarding_status', 'directrecruitment_onboarding_countries.created_by', 'directrecruitment_onboarding_countries.modified_by', 'directrecruitment_onboarding_countries.created_at', 'directrecruitment_onboarding_countries.updated_at', 'directrecruitment_onboarding_countries.deleted_at')
            ->find($onboardingCountryId);
    }
}
