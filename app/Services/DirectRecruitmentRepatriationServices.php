<?php

namespace App\Services;

use App\Models\DirectRecruitmentPostArrivalStatus;
use App\Models\WorkerRepatriation;
use App\Models\WorkerRepatriationAttachments;
use App\Models\Workers;
use App\Models\WorkerVisa;
use App\Models\DirectRecruitmentOnboardingCountry;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Events\WorkerQuotaUpdated;
use App\Events\KSMQuotaUpdated;

class DirectRecruitmentRepatriationServices
{

    public const USER_TYPE = 'Customer';
    public const REQUEST_COMPANY_ID = 'company_id';
    public const REQUEST_APPLICATION_ID = 'application_id';
    public const REQUEST_ONBOARDING_COUNTRY_ID = 'onboarding_country_id';

    public const FOMEMA_STATUS_UNFIT = 'Unfit';
    public const PLKS_STATUS_PENDING = 'Pending';
    public const POST_ARRIVAL_CANCELLED_STATUS = 'post_arrival_cancelled_status';
    public const DIRECT_RECRUITMENT_STATUS = 'Repatriated';
    public const FILE_TYPE_REPATRIATION = 'Repatriation';

    public const DEFAULT_INT_VALUE = 0;

    /**
     * @var DirectRecruitmentPostArrivalStatus $directRecruitmentPostArrivalStatus
     */
    private DirectRecruitmentPostArrivalStatus $directRecruitmentPostArrivalStatus;
    /**
     * @var WorkerRepatriation $workerRepatriation
     */
    private WorkerRepatriation $workerRepatriation;
    /**
     * @var WorkerRepatriationAttachments $workerRepatriationAttachments
     */
    private WorkerRepatriationAttachments $workerRepatriationAttachments;
    /**
     * @var workers $workers
     */
    private Workers $workers;
    /**
     * @var Storage $storage
     */
    private Storage $storage;
    /**
     * @var DirectRecruitmentExpensesServices $directRecruitmentExpensesServices
     */
    private DirectRecruitmentExpensesServices $directRecruitmentExpensesServices;
    /**
     * @var WorkerVisa $workerVisa
     */
    private WorkerVisa $workerVisa;
    /**
     * @var DirectRecruitmentOnboardingCountry $directRecruitmentOnboardingCountry
     */
    private DirectRecruitmentOnboardingCountry $directRecruitmentOnboardingCountry;

    /**
     * DirectRecruitmentRepatriationServices constructor method.
     * @param DirectRecruitmentPostArrivalStatus $directRecruitmentPostArrivalStatus The direct recruitment arrival services instance
     * @param WorkerRepatriation $workerRepatriation The worker repatriation instance
     * @param WorkerRepatriationAttachments $workerRepatriationAttachments The worker repatriation attachments instance
     * @param Workers $workers The workers instance
     * @param Storage $storage The storage instance
     * @param DirectRecruitmentExpensesServices $directRecruitmentExpensesServices The direct recruitment services instance
     * @param WorkerVisa $workerVisa The worker visa instance
     * @param DirectRecruitmentOnboardingCountry $directRecruitmentOnboardingCountry The direct recruitment onboarding country instance
     */
    public function __construct(
        DirectRecruitmentPostArrivalStatus $directRecruitmentPostArrivalStatus,
        WorkerRepatriation                 $workerRepatriation,
        WorkerRepatriationAttachments      $workerRepatriationAttachments,
        Workers                            $workers,
        Storage                            $storage,
        DirectRecruitmentExpensesServices  $directRecruitmentExpensesServices,
        WorkerVisa                         $workerVisa,
        DirectRecruitmentOnboardingCountry $directRecruitmentOnboardingCountry
    )
    {
        $this->directRecruitmentPostArrivalStatus = $directRecruitmentPostArrivalStatus;
        $this->workerRepatriation = $workerRepatriation;
        $this->workerRepatriationAttachments = $workerRepatriationAttachments;
        $this->workers = $workers;
        $this->storage = $storage;
        $this->directRecruitmentExpensesServices = $directRecruitmentExpensesServices;
        $this->workerVisa = $workerVisa;
        $this->directRecruitmentOnboardingCountry = $directRecruitmentOnboardingCountry;
    }

    /**
     * Validates the search data.
     *
     * @return array The validation rules.
     */
    public function searchValidation(): array
    {
        return [
            'search' => 'required|min:3'
        ];
    }

    /**
     * Perform create validation. Validates the input and returns the errors if validation fails.
     *
     * @return array The validation error messages if validation fails, otherwise false.
     */
    public function createValidation(): array
    {
        return [
            'flight_number' => 'required|regex:/^[a-zA-Z0-9]*$/',
            'flight_date' => 'required|date|date_format:Y-m-d|after:yesterday',
            'expenses' => 'required|regex:/^(([0-9]{0,6}+)(\.([0-9]{0,2}+))?)$/',
            'checkout_memo_reference_number' => 'required|regex:/^[0-9]*$/|max:23',
            'attachment.*' => 'mimes:jpeg,pdf,png|max:2048'
        ];
    }
    /**
     * @param $applicationId , $onboardingCountryId, $modifiedBy
     * @return void
     */
    /**
     * Update the post arrival status for a specific application in a specific onboarding country.
     *
     * @param int $applicationId The ID of the application.
     * @param int $onboardingCountryId The ID of the onboarding country.
     * @param string $modifiedBy The username of the user who modified the status.
     * @return void
     */
    public function updatePostArrivalStatus($applicationId, $onboardingCountryId, $modifiedBy): void
    {
        $this->directRecruitmentPostArrivalStatus->where([
            'application_id' => $applicationId,
            'onboarding_country_id' => $onboardingCountryId
        ])->update(['updated_on' => Carbon::now(), 'modified_by' => $modifiedBy]);
    }

    /**
     * Returns a paginated list of workers based on the provided request.
     *
     * @param array $request The request data containing filters and search parameters.
     * @return LengthAwarePaginator|array The paginated list of workers.
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
        $request['post_arrival_cancelled_status'] = Config::get('services.POST_ARRIVAL_CANCELLED_STATUS');

        return $this->workers
            ->leftJoin('worker_visa', 'worker_visa.worker_id', 'workers.id')
            ->leftJoin('worker_fomema', 'worker_fomema.worker_id', 'workers.id')
            ->leftjoin('directrecruitment_workers', 'directrecruitment_workers.worker_id', '=', 'workers.id')
            ->whereIn('workers.company_id', $request[self::REQUEST_COMPANY_ID])
            ->where(function ($query) use ($request) {
                if ($request['user']['user_type'] == self::USER_TYPE) {
                    $query->where('workers.crm_prospect_id', '=', $request['user']['reference_id']);
                }
            })
            ->where([
                'directrecruitment_workers.application_id' => $request[self::REQUEST_APPLICATION_ID],
                'directrecruitment_workers.onboarding_country_id' => $request[self::REQUEST_ONBOARDING_COUNTRY_ID]
            ])
            ->where(function ($query) use ($request) {
                $query->where([
                    ['worker_fomema.fomema_status', self::FOMEMA_STATUS_UNFIT],
                    ['workers.plks_status', self::PLKS_STATUS_PENDING]
                ])
                    ->orWhere([
                        ['workers.cancel_status', $request[self::POST_ARRIVAL_CANCELLED_STATUS]]
                    ]);
            })
            ->whereNull('workers.replace_worker_id')
            ->where(function ($query) use ($request) {
                $this->applySearchQuery($query, $request);
            })
            ->select('workers.id', 'directrecruitment_workers.application_id', 'directrecruitment_workers.onboarding_country_id', 'workers.name', 'worker_visa.ksm_reference_number', 'workers.passport_number', 'worker_visa.entry_visa_valid_until', 'worker_fomema.fomema_status', 'workers.date_of_birth', 'workers.gender', 'directrecruitment_workers.agent_id', 'workers.plks_status', 'workers.cancel_status', 'workers.directrecruitment_status')->selectRaw("(CASE WHEN (workers.directrecruitment_status = 'Repatriated') THEN workers.directrecruitment_status
            WHEN (workers.directrecruitment_status = 'Cancelled') THEN workers.directrecruitment_status
            ELSE worker_fomema.fomema_status END) as status")->distinct('workers.id')
            ->orderBy('workers.id', 'desc')
            ->paginate(Config::get('services.paginate_worker_row'));
    }

    /**
     * Checks the count of workers based on the provided request.
     *
     * @param $request - The request object containing the workers and company ID.
     * @return int The count of workers based on the request.
     */
    public function checkWorkerCount($request): int
    {
        return $this->workers->whereIn('id', $request['workers'])
            ->where('company_id', $request[self::REQUEST_COMPANY_ID])
            ->count();
    }

    /**
     * Checks the application based on the given request.
     *
     * @param array $request The request data containing company ID and onboarding country ID.
     * @return Model|null The application model if found, otherwise null.
     */
    public function checkApplication($request)
    {
        return $this->directRecruitmentOnboardingCountry
            ->join('directrecruitment_applications', function ($join) use ($request) {
                $join->on('directrecruitment_onboarding_countries.application_id', '=', 'directrecruitment_applications.id')
                    ->where('directrecruitment_applications.company_id', $request[self::REQUEST_COMPANY_ID]);
            })->find($request[self::REQUEST_ONBOARDING_COUNTRY_ID]);
    }

    /**
     * Updates the direct recruitment status of the workers.
     *
     * @param array $workersId The IDs of the workers to update.
     * @param $fomemaValidUntil - The new FOMEMA valid until date.
     * @param int $modifiedBy The ID of the user who modified the status.
     * @return void
     */
    public function updateWorkerDirectRecruitmentStatus(array $workersId, $fomemaValidUntil, int $modifiedBy): void
    {
        $this->workers->whereIn('id', $workersId)
            ->update([
                'directrecruitment_status' => self::DIRECT_RECRUITMENT_STATUS,
                'fomema_valid_until' => $fomemaValidUntil,
                'modified_by' => $modifiedBy
            ]);
    }

    /**
     * Updates the repatriation process.
     *
     * @param array $request The request data.
     * @return array|bool If validation fails, returns an array with error messages. Otherwise, returns true.
     */
    public function updateRepatriation($request): array|bool
    {
        $validationResult = $this->validateRequest($request);
        if (is_array($validationResult)) return $validationResult;

        $workersProcessingResult = $this->processWorkers($request);
        if (is_array($workersProcessingResult)) return $workersProcessingResult;

        $this->updatePostArrivalStatus($request[self::REQUEST_APPLICATION_ID], $request[self::REQUEST_ONBOARDING_COUNTRY_ID], $request['modified_by']);

        // ADD OTHER EXPENSES - Onboarding - Repatriation Expenses (RM)
        $this->addExpenses($request);

        return true;
    }

    /**
     * Validates the request data.
     *
     * @param mixed $request The request object.
     * @return array|bool Returns an array of validation errors if the validation fails, otherwise returns false.
     */
    private function validateRequest($request): array|bool
    {
        $validator = Validator::make($request->toArray(), $this->createValidation());
        if ($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }

        return false;
    }

    /**
     * Process the workers' data.
     *
     * @param array $request The request data.
     * @return array|bool Returns an array containing 'InvalidUser' => true if worker or application is invalid,
     *                   or false if request['workers'] is empty.
     */
    private function processWorkers($request): array|bool
    {
        if (!empty($request['workers'])) {
            $request['workers'] = explode(',', $request['workers']);

            if (!$this->validWorkerAndApplication($request)) return ['InvalidUser' => true];

            foreach ($request['workers'] as $workerId) {
                $this->createWorkerRepatriation($request, $workerId);
                $this->uploadAttachments($request, $workerId);
            }

            $this->updateWorkerDirectRecruitmentStatus($request['workers'], $request['fomema_valid_until'], $request['modified_by']);

            $this->updateUtilisedQuota($request);

            // update utilised quota in onboarding country
            event(new WorkerQuotaUpdated($request[self::REQUEST_ONBOARDING_COUNTRY_ID], count($request['workers']), 'decrement'));
        }

        return false;
    }

    /**
     * Validates the worker and application data.
     *
     * @param array $request The request data containing 'workers', 'company_id', 'onboarding_country_id', and 'application_id'.
     *
     * @return bool Returns true if the worker and application data is valid, false otherwise.
     */
    private function validWorkerAndApplication($request): bool
    {
        $workerCompanyCount = $this->checkWorkerCount($request);
        if ($workerCompanyCount != count($request['workers'])) return false;

        $applicationCheck = $this->checkForApplication($request['company_id'], $request['onboarding_country_id']);
        if (is_null($applicationCheck) || ($applicationCheck->application_id != $request[self::REQUEST_APPLICATION_ID])) return false;

        return true;
    }

    /**
     * Upload attachments.
     *
     * @param $request - The request object.
     * @param int $workerId The worker ID.
     * @return void
     */
    private function uploadAttachments($request, $workerId): void
    {
        if (request()->hasFile('attachment')) {
            foreach ($request->file('attachment') as $file) {
                $this->uploadFiles($file, $workerId, $request['modified_by']);
            }
        }
    }

    /**
     * Updates the utilised quota for workers.
     *
     * @param array $request The request data containing the worker IDs.
     * @return array The updated worker details.
     */
    private function updateUtilisedQuota($request): array
    {
        $workerDetails = [];
        foreach ($request['workers'] as $worker) {
            $ksmDetails = $this->workerVisa->where('worker_id', $worker)->first(['ksm_reference_number']);
            $workerDetails[$worker] = $ksmDetails->ksm_reference_number;
        }

        $ksmCount = array_count_values($workerDetails);
        foreach ($ksmCount as $key => $value) {
            event(new KSMQuotaUpdated($request[self::REQUEST_ONBOARDING_COUNTRY_ID], $key, $value, 'decrement'));
        }

        return $workerDetails;
    }

    /**
     * Create a worker repatriation record.
     *
     * @param Request $request The request data containing the worker's information.
     * @param int $workerId The ID of the worker.
     * @return void
     */
    public function createWorkerRepatriation($request, int $workerId): void
    {
        $this->workerRepatriation->create([
            'worker_id' => $workerId,
            'flight_number' => $request['flight_number'],
            'flight_date' => $request['flight_date'],
            'expenses' => $request['expenses'],
            'checkout_memo_reference_number' => $request['checkout_memo_reference_number'],
            'created_by' => $request['modified_by'],
            'modified_by' => $request['modified_by']
        ]);
    }

    /**
     * Uploads multiple files and saves their information to the database.
     *
     * @param $file The array of files to upload.
     * @param int $workerId The ID of the worker.
     * @param int $modifiedBy The ID of the user who modified the files.
     * @return void
     */
    public function uploadFiles($file, $workerId, $modifiedBy)
    {
        $fileName = $file->getClientOriginalName();
        $filePath = 'directRecruitment/workers/repatriation/' . $workerId . '/' . $fileName;

        $linode = $this->storage::disk('linode');
        $linode->put($filePath, file_get_contents($file));

        $fileUrl = $linode->url($filePath);

        $this->workerRepatriationAttachments->create([
            'file_id' => $workerId,
            'file_name' => $fileName,
            'file_type' => self::FILE_TYPE_REPATRIATION,
            'file_url' => $fileUrl,
            'created_by' => $modifiedBy,
            'modified_by' => $modifiedBy
        ]);
    }

    /**
     * Adds expenses to the application.
     *
     * @param Request $request The request data containing expenses information.
     *                      The array structure should include the following keys:
     *                      - expenses_application_id (optional): The ID of the application.
     *                                                             If not provided, it will be set to DEFAULT_INT_VALUE.
     *                      - expenses_title: The title of the expenses.
     *                                        The value is fetched from the 'services.OTHER_EXPENSES_TITLE' configuration.
     *                      - expenses_payment_reference_number (optional): The payment reference number.
     *                                                                       If not provided, it will be set to an empty string.
     *                      - expenses_payment_date: The payment date (Carbon object)
     *                                              set to the current date and time if not provided.
     *                      - expenses_amount: The amount of expenses.
     *                                         The value is fetched from the 'expenses' key in the request array.
     *                                         If not provided, it will be set to DEFAULT_INT_VALUE.
     *                      - expenses_remarks (optional): Any additional remarks for the expenses.
     *                                                      If not provided, it will be set to an empty string.
     *
     * @return void
     */
    public function addExpenses($request): void
    {
        $request['expenses_application_id'] = $request[self::REQUEST_APPLICATION_ID] ?? self::DEFAULT_INT_VALUE;
        $request['expenses_title'] = Config::get('services.OTHER_EXPENSES_TITLE')[7];
        $request['expenses_payment_reference_number'] = $request['checkout_memo_reference_number'] ?? '';
        $request['expenses_payment_date'] = Carbon::now();
        $request['expenses_amount'] = $request['expenses'] ?? self::DEFAULT_INT_VALUE;
        $request['expenses_remarks'] = $request['remarks'] ?? '';
        $this->directRecruitmentExpensesServices->addOtherExpenses($request);
    }

    /**
     * @param $request
     * @return mixed
     */
    public function workersListExport($request): mixed
    {
        if (!empty($request['search'])) {
            $validator = Validator::make($request, $this->searchValidation());
            if ($validator->fails()) {
                return [
                    'error' => $validator->errors()
                ];
            }
        }
        $request['post_arrival_cancelled_status'] = Config::get('services.POST_ARRIVAL_CANCELLED_STATUS');
        return $this->workers
            ->leftJoin('worker_visa', 'worker_visa.worker_id', 'workers.id')
            ->leftJoin('worker_fomema', 'worker_fomema.worker_id', 'workers.id')
            ->leftjoin('directrecruitment_workers', 'directrecruitment_workers.worker_id', '=', 'workers.id')
            ->whereIn('workers.company_id', $request[self::REQUEST_COMPANY_ID])
            ->where(function ($query) use ($request) {
                if ($request['user']['user_type'] == 'Customer') {
                    $query->where('workers.crm_prospect_id', '=', $request['user']['reference_id']);
                }
            })
            ->where([
                'directrecruitment_workers.application_id' => $request[self::REQUEST_APPLICATION_ID],
                'directrecruitment_workers.onboarding_country_id' => $request[self::REQUEST_ONBOARDING_COUNTRY_ID]
            ])
            ->where(function ($query) use ($request) {
                $query->where([
                    ['worker_fomema.fomema_status', self::FOMEMA_STATUS_UNFIT],
                    ['workers.plks_status', self::PLKS_STATUS_PENDING]
                ])
                    ->orWhere([
                        ['workers.cancel_status', $request[self::POST_ARRIVAL_CANCELLED_STATUS]]
                    ]);
            })
            ->whereNull('workers.replace_worker_id')
            ->where(function ($query) use ($request) {
                $this->applySearchQuery($query, $request);
            })
            ->select('workers.id', 'directrecruitment_workers.application_id', 'directrecruitment_workers.onboarding_country_id', 'workers.name', 'worker_visa.ksm_reference_number', 'workers.passport_number', 'worker_visa.entry_visa_valid_until', 'workers.date_of_birth', 'workers.gender', 'directrecruitment_workers.agent_id')
            ->selectRaw("(CASE WHEN (workers.directrecruitment_status = 'Repatriated') THEN workers.directrecruitment_status
            WHEN (workers.directrecruitment_status = 'Cancelled') THEN workers.directrecruitment_status
            ELSE worker_fomema.fomema_status END) as status")
            ->distinct('workers.id')
            ->orderBy('workers.id', 'desc')
            ->get();
    }

    /**
     * Applies the search query to the given query builder.
     *
     * @param $query - The query builder to apply the search query to.
     * @param array $request The request containing the search parameter.
     * @return void
     */
    private function applySearchQuery($query, $request)
    {
        if (!empty($request['search'])) {
            $query->where('workers.name', 'like', '%' . $request['search'] . '%')
                ->orWhere('worker_visa.ksm_reference_number', 'like', '%' . $request['search'] . '%')
                ->orWhere('workers.passport_number', 'like', '%' . $request['search'] . '%');
        }
    }

    /**
     * Checks if there is an application associated with the given company and onboarding country.
     *
     * @param int $companyId The ID of the company.
     * @param int $onboardingCountryId The ID of the onboarding country.
     * @return mixed|null The onboarding record data if found, otherwise null.
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
