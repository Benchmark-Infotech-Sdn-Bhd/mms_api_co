<?php

namespace App\Services;

use App\Models\Workers;
use App\Models\WorkerVisa;
use App\Models\DirectrecruitmentArrival;
use App\Models\WorkerArrival;
use App\Models\CancellationAttachment;
use App\Models\DirectRecruitmentPostArrivalStatus;
use App\Models\DirectRecruitmentOnboardingCountry;
use Exception;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Events\WorkerQuotaUpdated;
use App\Events\KSMQuotaUpdated;

class DirectRecruitmentArrivalServices
{
    /**
     * @var workers
     */
    private Workers $workers;

    /**
     * @var WorkerVisa
     */
    private WorkerVisa $workerVisa;

    /**
     * @var DirectrecruitmentArrival
     */
    private DirectrecruitmentArrival $directrecruitmentArrival;

    /**
     * @var WorkerArrival
     */
    private WorkerArrival $workerArrival;

    /**
     * @var CancellationAttachment
     */
    private CancellationAttachment $cancellationAttachment;

    /**
     * @var DirectRecruitmentOnboardingCountryServices
     */
    private DirectRecruitmentOnboardingCountryServices $directRecruitmentOnboardingCountryServices;

    /**
     * @var DirectRecruitmentPostArrivalStatus
     */
    private DirectRecruitmentPostArrivalStatus $directRecruitmentPostArrivalStatus;

    /**
     * @var Storage
     */
    private Storage $storage;
    /**
     * @var DirectRecruitmentOnboardingCountry
     */
    private DirectRecruitmentOnboardingCountry $directRecruitmentOnboardingCountry;

    /**
     * Class constructor.
     *
     * @param Workers $workers The workers instance.
     * @param WorkerVisa $workerVisa The worker visa instance.
     * @param DirectrecruitmentArrival $directrecruitmentArrival The direct recruitment arrival instance.
     * @param WorkerArrival $workerArrival The worker arrival instance.
     * @param CancellationAttachment $cancellationAttachment The cancellation attachment instance.
     * @param DirectRecruitmentOnboardingCountryServices $directRecruitmentOnboardingCountryServices The direct recruitment onboarding country services instance.
     * @param DirectRecruitmentPostArrivalStatus $directRecruitmentPostArrivalStatus The direct recruitment post arrival status instance.
     * @param Storage $storage The storage instance.
     * @param DirectRecruitmentOnboardingCountry $directRecruitmentOnboardingCountry The direct recruitment onboarding country instance.
     *
     * @return void
     */
    public function __construct(
        Workers                                    $workers,
        WorkerVisa                                 $workerVisa,
        DirectrecruitmentArrival                   $directrecruitmentArrival,
        WorkerArrival                              $workerArrival,
        CancellationAttachment                     $cancellationAttachment,
        DirectRecruitmentOnboardingCountryServices $directRecruitmentOnboardingCountryServices,
        DirectRecruitmentPostArrivalStatus         $directRecruitmentPostArrivalStatus,
        Storage                                    $storage,
        DirectRecruitmentOnboardingCountry         $directRecruitmentOnboardingCountry
    )
    {
        $this->workers = $workers;
        $this->workerVisa = $workerVisa;
        $this->directrecruitmentArrival = $directrecruitmentArrival;
        $this->workerArrival = $workerArrival;
        $this->cancellationAttachment = $cancellationAttachment;
        $this->directRecruitmentOnboardingCountryServices = $directRecruitmentOnboardingCountryServices;
        $this->directRecruitmentPostArrivalStatus = $directRecruitmentPostArrivalStatus;
        $this->storage = $storage;
        $this->directRecruitmentOnboardingCountry = $directRecruitmentOnboardingCountry;
    }

    /**
     * @return array
     */
    public function submitValidation(): array
    {
        return
            [
                'application_id' => 'required',
                'onboarding_country_id' => 'required',
                'flight_date' => 'required|date|date_format:Y-m-d',
                'arrival_time' => 'required',
                'flight_number' => 'required'
            ];
    }

    /**
     * @return array
     */
    public function updateValidation(): array
    {
        return [
            'arrival_id' => 'required',
            'flight_date' => 'required|date|date_format:Y-m-d',
            'arrival_time' => 'required',
            'flight_number' => 'required'
        ];
    }

    /**
     * @return array
     */
    public function updateWorkersValidation(): array
    {
        return [
            'arrival_id' => 'required'
        ];
    }

    /**
     * @return array
     */
    public function cancelValidation(): array
    {
        return [
            'arrival_id' => 'required'
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
     * Retrieves a paginated list of worker arrivals based on the specified criteria.
     *
     * @param array $request The request data containing the following keys:
     *                      - application_id: The application ID
     *                      - onboarding_country_id: The onboarding country ID
     *                      - company_id: An array of company IDs
     *
     * @return mixed The paginated list of worker arrivals
     */
    public function list($request): mixed
    {
        return $this->workerArrival
            ->leftJoin('directrecruitment_arrival', 'worker_arrival.arrival_id', 'directrecruitment_arrival.id')
            ->where([
                ['directrecruitment_arrival.application_id', $request['application_id']],
                ['directrecruitment_arrival.onboarding_country_id', $request['onboarding_country_id']]
            ])
            ->join('directrecruitment_applications', function ($join) use ($request) {
                $join->on('directrecruitment_arrival.application_id', '=', 'directrecruitment_applications.id')
                    ->whereIn('directrecruitment_applications.company_id', $request['company_id']);
            })
            ->where('worker_arrival.arrival_status', '!=', 'Postponed')
            ->select('directrecruitment_arrival.id', 'directrecruitment_arrival.application_id', 'directrecruitment_arrival.onboarding_country_id', 'directrecruitment_arrival.item_name', 'directrecruitment_arrival.flight_date', 'directrecruitment_arrival.arrival_time', 'directrecruitment_arrival.flight_number', 'worker_arrival.arrival_status', DB::raw('COUNT(worker_arrival.worker_id) as workers'))
            ->groupBy('directrecruitment_arrival.id', 'directrecruitment_arrival.application_id', 'directrecruitment_arrival.onboarding_country_id', 'directrecruitment_arrival.item_name', 'directrecruitment_arrival.flight_date', 'directrecruitment_arrival.arrival_time', 'directrecruitment_arrival.flight_number', 'worker_arrival.arrival_status')
            ->orderBy('directrecruitment_arrival.id', 'DESC')
            ->paginate(Config::get('services.paginate_row'));
    }

    /**
     * Retrieves the direct recruitment data for a specific arrival.
     *
     * @param array $request The request data containing the 'company_id' and 'arrival_id'
     *
     * @return mixed Returns the direct recruitment data for the given arrival
     */
    public function show($request): mixed
    {
        return $this->directrecruitmentArrival
            ->join('directrecruitment_applications', function ($join) use ($request) {
                $join->on('directrecruitment_arrival.application_id', '=', 'directrecruitment_applications.id')
                    ->whereIn('directrecruitment_applications.company_id', $request['company_id']);
            })
            ->where('directrecruitment_arrival.id', $request['arrival_id'])
            ->select('directrecruitment_arrival.application_id', 'directrecruitment_arrival.onboarding_country_id', 'directrecruitment_arrival.item_name', 'directrecruitment_arrival.flight_date', 'directrecruitment_arrival.arrival_time', 'directrecruitment_arrival.flight_number', 'directrecruitment_arrival.status', 'directrecruitment_arrival.remarks')
            ->get();
    }


    /**
     * Get the list of workers for submission.
     *
     * @param mixed $request The request data.
     *
     * @return LengthAwarePaginator The list of workers for submission.
     * @throws ValidationException
     */
    public function workersListForSubmit($request)
    {
        $this->validateRequest($request);
        $query = $this->buildQuery($request);

        return $query->select('workers.id', 'workers.name', 'workers.gender', 'workers.date_of_birth', 'workers.passport_number', 'directrecruitment_workers.application_id', 'directrecruitment_workers.onboarding_country_id', 'directrecruitment_workers.agent_id', 'worker_visa.ksm_reference_number', 'worker_visa.calling_visa_reference_number', 'worker_visa.submitted_on')
            ->distinct('workers.id')
            ->orderBy('workers.id', 'DESC')
            ->paginate(Config::get('services.paginate_worker_row'));
    }

    /**
     * Validate the given request.
     *
     * @param array $request The request data to be validated.
     * @throws ValidationException If the validation fails.
     */
    private function validateRequest($request)
    {
        if (!empty($request['search'])) {
            $validator = Validator::make($request, $this->searchValidation());
            if ($validator->fails()) {
                throw new ValidationException($validator, 'Validation failed', [$validator->errors()]);
            }
        }
    }

    /**
     * Build the query based on the request parameters.
     *
     * @param array $request The request parameters.
     * @return Builder The built query.
     */
    private function buildQuery($request)
    {
        return $this->workers
            ->join('worker_visa', function ($join) {
                $join->on('worker_visa.worker_id', '=', 'workers.id')
                    ->where('worker_visa.dispatch_status', '=', 'Processed');
            })
            ->leftjoin('directrecruitment_workers', 'directrecruitment_workers.worker_id', '=', 'workers.id')
            ->leftJoin('worker_arrival', 'worker_arrival.worker_id', 'worker_visa.worker_id')
            ->whereIn('workers.company_id', $request['company_id'])
            ->where(function ($query) use ($request) {
                if ($request['user']['user_type'] == 'Customer') {
                    $query->where('workers.crm_prospect_id', '=', $request['user']['reference_id']);
                }
            })
            ->whereNull('worker_arrival.arrival_id')
            ->where($this->getSearchConditions($request));
    }

    /**
     * Retrieves the search conditions based on the given request array.
     *
     * @param array $request The request array containing the search parameters.
     * @return array The array of conditions used for searching.
     */
    private function getSearchConditions($request)
    {
        $conditions = [
            ['directrecruitment_workers.application_id', $request['application_id']],
            ['directrecruitment_workers.onboarding_country_id', $request['onboarding_country_id']],
            ['workers.cancel_status', 0]
        ];

        if (!empty($request['calling_visa_reference_number'])) {
            $conditions[] = ['worker_visa.calling_visa_reference_number', $request['calling_visa_reference_number']];
        }
        if (!empty($request['search'])) {
            foreach (['workers.name', 'worker_visa.ksm_reference_number', 'workers.passport_number', 'worker_visa.calling_visa_reference_number'] as $field) {
                $conditions[] = [$field, 'like', '%' . $request['search'] . '%', 'or'];
            }
        }

        return $conditions;
    }

    /**
     * Get the list of workers for update.
     *
     * @param $request - The request object containing necessary data for filtering the workers list.
     * @return LengthAwarePaginator The paginated list of workers based on the applied conditions.
     */
    public function workersListForUpdate($request)
    {
        $query = $this->buildBaseQuery();
        $query = $this->applyCompanyCondition($query, $request);
        $query = $this->applyUserTypeCondition($query, $request);
        $query = $this->applyApplicationConditions($query, $request);
        $query = $this->applyArrivalStatusCondition($query);
        $query = $this->applySearchCondition($query, $request);
        $query = $this->selectColumns($query);

        return $query->distinct('workers.id')
            ->orderBy('workers.id', 'DESC')
            ->paginate(Config::get('services.paginate_worker_row'));
    }

    /**
     * Build the base query for worker search.
     *
     * This method builds the base query to search for workers by joining tables:
     * - workers and worker_visa on worker_visa.worker_id = workers.id and worker_visa.dispatch_status = 'Processed'
     * - worker_arrival on worker_arrival.worker_id = worker_visa.worker_id
     * - directrecruitment_workers on directrecruitment_workers.worker_id = workers.id
     *
     * @return Builder The base query for worker search
     */
    private function buildBaseQuery()
    {
        return $this->workers
            ->join('worker_visa', function ($join) {
                $join->on('worker_visa.worker_id', '=', 'workers.id')
                    ->where('worker_visa.dispatch_status', '=', 'Processed');
            })
            ->leftJoin('worker_arrival', 'worker_arrival.worker_id', 'worker_visa.worker_id')
            ->leftjoin('directrecruitment_workers', 'directrecruitment_workers.worker_id', '=', 'workers.id');
    }

    /**
     * Apply a condition based on the company ID for the given query.
     *
     * @param Builder $query The query builder instance
     * @param array $request The request data containing the company ID(s)
     *
     * @return Builder The modified query builder instance
     */
    private function applyCompanyCondition($query, $request)
    {
        return $query->whereIn('workers.company_id', $request['company_id']);
    }

    /**
     * Applies a user type condition to the given query.
     *
     * @param $query
     * @param $request
     * @return mixed [object] The modified query object.
     */
    private function applyUserTypeCondition($query, $request)
    {
        if ($request['user']['user_type'] == 'Customer') {
            return $query->where('workers.crm_prospect_id', '=', $request['user']['reference_id']);
        }
        return $query;
    }

    /**
     * Apply the application conditions to the given query.
     *
     * @param Builder $query The query to apply the conditions to.
     * @param array $request The request data containing the conditions.
     *
     * @return Builder  The modified query with the applied conditions.
     */
    private function applyApplicationConditions($query, $request)
    {
        return $query->where([
            ['directrecruitment_workers.application_id', $request['application_id']],
            ['directrecruitment_workers.onboarding_country_id', $request['onboarding_country_id']],
            ['worker_arrival.arrival_id', $request['arrival_id']]
        ]);
    }

    /**
     * Apply arrival status condition to the query.
     *
     * @param Builder $query The query instance.
     * @return Builder The modified query instance.
     */
    private function applyArrivalStatusCondition($query)
    {
        return $query->where('worker_arrival.arrival_status', '!=', 'Postponed');
    }

    /**
     * Apply search condition to the query.
     *
     * @param $query
     * @param $request
     * @return mixed
     */
    private function applySearchCondition($query, $request)
    {
        if (isset($request['calling_visa_reference_number']) && $request['calling_visa_reference_number']) {
            $query = $query->where('worker_visa.calling_visa_reference_number', $request['calling_visa_reference_number']);
        }
        if (isset($request['search']) && $request['search']) {
            $query = $query->where('workers.name', 'like', '%' . $request['search'] . '%')
                ->orWhere('worker_visa.ksm_reference_number', 'like', '%' . $request['search'] . '%')
                ->orWhere('workers.passport_number', 'like', '%' . $request['search'] . '%')
                ->orWhere('worker_visa.calling_visa_reference_number', 'like', '%' . $request['search'] . '%');
        }
        return $query;
    }

    /**
     * Select columns for the given query.
     *
     * @param Builder $query The query instance.
     *
     * @return Builder The updated query instance with selected columns.
     */
    private function selectColumns($query)
    {
        return $query->select('workers.id', 'workers.name', 'workers.gender', 'workers.date_of_birth', 'workers.passport_number', 'directrecruitment_workers.application_id', 'directrecruitment_workers.onboarding_country_id', 'directrecruitment_workers.agent_id', 'worker_visa.ksm_reference_number', 'worker_visa.calling_visa_reference_number', 'worker_visa.submitted_on', 'worker_arrival.arrival_status');
    }

    /**
     * @param $request
     * @return bool|array
     */
    /**
     * Submit the request.
     *
     * @param array $request The request data.
     *
     * @return bool|array Returns false if request validation fails, otherwise returns the result of submitWithWorkers().
     */
    public function submit($request): bool|array
    {
        $validator = Validator::make($request, $this->submitValidation());
        if ($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }
        if (empty($request['workers'])) {
            return false;
        }
        return $this->submitWithWorkers($request);
    }

    /**
     * Submit the workers with the given request.
     *
     * @param array $request The request data.
     * @return array|bool The result of the submission.
     */
    protected function submitWithWorkers($request)
    {
        if (!$this->validateWorkers($request)) {
            return ['InvalidUser' => true];
        }

        $applicationCheck = $this->checkForOnboardingCountryApplication($request['company_id'], $request['onboarding_country_id']);
        if (is_null($applicationCheck) || ($applicationCheck->application_id != $request['application_id'])) {
            return ['InvalidUser' => true];
        }

        $directrecruitmentArrival = $this->createDirectRecruitmentArrival($request);
        $request['arrival_id'] = $directrecruitmentArrival->id ?? 0;

        return $this->updateWorkerArrivalAndStatus($request);
    }

    /**
     * Validates the workers based on the request data.
     *
     * @param array $request The request data containing the worker IDs and company ID.
     *
     * @return bool Returns true if the workers belong to the specified company, otherwise false.
     */
    protected function validateWorkers($request)
    {
        $workerCompanyCount = $this->workers->whereIn('id', $request['workers'])
            ->where('company_id', $request['company_id'])
            ->count();
        return $workerCompanyCount == count($request['workers']);
    }

    /**
     * Creates a direct recruitment arrival record.
     *
     * @param array $request The request data containing the arrival details.
     *
     * @return \App\Models\DirectRecruitmentArrival The created direct recruitment arrival record.
     */
    protected function createDirectRecruitmentArrival($request)
    {
        return $this->directrecruitmentArrival->create([
            'application_id' => $request['application_id'] ?? 0,
            'onboarding_country_id' => $request['onboarding_country_id'] ?? 0,
            'item_name' => 'Arrival',
            'flight_date' => $request['flight_date'],
            'arrival_time' => $request['arrival_time'],
            'flight_number' => $request['flight_number'],
            'remarks' => $request['remarks'],
            'status' => $request['status'] ?? 'Not Arrived',
            'created_by' => $request['created_by'] ?? 0,
            'modified_by' => $request['created_by'] ?? 0
        ]);
    }

    /**
     * Updates the arrival and status of the workers.
     *
     * @param array $request The request containing the necessary data.
     *                       The array should include the following keys:
     *                       - workers (array): An array of worker IDs.
     *                       - arrival_id (int): The ID of the arrival.
     *                       - status (string, optional): The arrival status. Defaults to 'Not Arrived'.
     *                       - created_by (string): The user who created the arrival.
     *                       - application_id (int): The ID of the application.
     *                       - onboarding_country_id (int): The ID of the onboarding country.
     *
     * @return bool Returns true if the update is successful, false otherwise.
     */
    protected function updateWorkerArrivalAndStatus($request)
    {
        foreach ($request['workers'] as $workerId) {
            $this->workerArrival->updateOrCreate(
                [
                    'worker_id' => $workerId,
                    'arrival_id' => $request['arrival_id']
                ],
                [
                    'arrival_status' => $request['status'] ?? 'Not Arrived',
                    'created_by' => $request['created_by'],
                    'modified_by' => $request['created_by']
                ]
            );
        }

        $this->directRecruitmentPostArrivalStatus->create([
            'application_id' => $request['application_id'],
            'onboarding_country_id' => $request['onboarding_country_id'],
            'item' => 'Post Arrival',
            'updated_on' => Carbon::now(),
            'created_by' => $request['created_by'],
            'modified_by' => $request['created_by']
        ]);

        $this->workers->whereIn('id', $request['workers'])->update(['directrecruitment_status' => 'Not Arrived', 'modified_by' => $request['created_by']]);

        $onBoardingStatus['application_id'] = $request['application_id'];
        $onBoardingStatus['country_id'] = $request['onboarding_country_id'];
        $onBoardingStatus['onboarding_status'] = 6; //Agent Added

        $this->directRecruitmentOnboardingCountryServices->onboarding_status_update($onBoardingStatus);

        return true;
    }

    /**
     * Update the direct recruitment arrival record.
     *
     * @param array $request The request data for updating the record.
     * @return bool|array Returns true if the record is successfully updated. Otherwise, returns an array with error details.
     */
    public function update($request): bool|array
    {
        $validator = Validator::make($request, $this->updateValidation());
        if ($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }

        $directRecruitmentArrival = $this->loadAndModifyDirectRecruitmentArrival($request);
        if (is_null($directRecruitmentArrival)) {
            return [
                'InvalidUser' => true
            ];
        }

        $directRecruitmentArrival->save();
        return true;
    }

    /**
     * Loads and modifies direct recruitment arrival record based on the given request data.
     *
     * @param array $request The array containing the request data.
     *                      Expected keys:
     *                          - company_id: The ID of the company.
     *                          - arrival_id: The ID of the arrival.
     *                          - flight_date: (optional) The modified flight date.
     *                          - arrival_time: (optional) The modified arrival time.
     *                          - flight_number: (optional) The modified flight number.
     *                          - status: (optional) The modified status.
     *                          - remarks: (optional) The modified remarks.
     *                          - modified_by: (optional) The modified by value.
     * @return mixed|null The modified direct recruitment arrival record if found, otherwise null.
     */
    private function loadAndModifyDirectRecruitmentArrival($request)
    {
        $directRecruitmentArrival = $this->directrecruitmentArrival
            ->join('directrecruitment_applications', function ($join) use ($request) {
                $join->on('directrecruitment_arrival.application_id', '=', 'directrecruitment_applications.id')
                    ->where('directrecruitment_applications.company_id', $request['company_id']);
            })
            ->where('directrecruitment_arrival.id', $request['arrival_id'])
            ->first('directrecruitment_arrival.*');

        if (is_null($directRecruitmentArrival)) {
            return null;
        }

        $directRecruitmentArrival->flight_date = $request['flight_date'] ?? $directRecruitmentArrival->flight_date;
        $directRecruitmentArrival->arrival_time = $request['arrival_time'] ?? $directRecruitmentArrival->arrival_time;
        $directRecruitmentArrival->flight_number = $request['flight_number'] ?? $directRecruitmentArrival->flight_number;
        $directRecruitmentArrival->status = $request['status'] ?? $directRecruitmentArrival->status;
        $directRecruitmentArrival->remarks = $request['remarks'] ?? $directRecruitmentArrival->remarks;
        $directRecruitmentArrival->modified_by = $request['modified_by'] ?? $directRecruitmentArrival->modified_by;

        return $directRecruitmentArrival;
    }

    /**
     * Cancels a worker.
     *
     * @param Request $request The request object.
     *
     * @return bool|array Returns true if the worker is successfully cancelled, otherwise returns an array
     *                   with error messages if the validation fails or worker details are invalid.
     * @throws ValidationException
     */
    public function cancelWorker($request): bool|array
    {
        $inputs = $request->all();
        $authenticatedUser = $this->authenticateUser();
        $inputs['created_by'] = $authenticatedUser['id'];
        $inputs['company_id'] = $authenticatedUser['company_id'];

        $this->validateCancelRequest($request, $this->cancelValidation());

        if (isset($request['workers']) && !empty($request['workers'])) {
            $workerIdArray = $this->splitWorkers($request['workers']);
            $this->verifyWorkers($workerIdArray, $inputs['company_id']);

            $applicationVerification = $this->checkForArrivalApplication($inputs['company_id'], $request['arrival_id']);
            if (is_null($applicationVerification)) {
                return ['InvalidUser' => true];
            }

            if (is_array($workerIdArray)) {
                return $this->processCancellation($request, $inputs, $workerIdArray);
            }
        }

        return false;
    }

    /**
     * Authenticate the user using JWT token.
     *
     * @return Authenticatable|null
     */
    private function authenticateUser()
    {
        return JWTAuth::parseToken()->authenticate();
    }

    /**
     * Validate the cancel request based on the given validation rules.
     *
     * @param Request $request The request object containing the cancel data.
     * @param array $validationRules The validation rules to be applied on the cancel request.
     * @return void
     * @throws ValidationException if validation fails.
     */
    private function validateCancelRequest($request, $validationRules)
    {
        $validator = Validator::make($request->toArray(), $validationRules);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    /**
     * Split the workers from the request string.
     *
     * @param string $workersRequest The request string containing the workers.
     *                              The workers must be separated by commas.
     *
     * @return array An array containing the split workers.
     */
    private function splitWorkers($workersRequest)
    {
        return explode(",", $workersRequest);
    }

    /**
     * Verifies if the given worker ids belong to the specified company.
     *
     * @param array $workerIdArray An array of worker IDs to verify.
     * @param int $companyId The ID of the company to check against.
     *
     * @return array|null|void Returns an array with the key 'InvalidUser' set to true if the worker ids are invalid,
     *                   otherwise null is returned.
     */
    private function verifyWorkers($workerIdArray, $companyId)
    {
        $workerCompanyCount = $this->workers->whereIn('id', $workerIdArray)
            ->where('company_id', $companyId)
            ->count();

        if ($workerCompanyCount != count($workerIdArray)) {
            return ['InvalidUser' => true];
        }
    }

    /**
     * Process cancellation of workers.
     *
     * @param Request $request The request object containing the cancellation details.
     * @param array $params Additional parameters required for processing the cancellation.
     * @param array $workers The array of worker IDs to be cancelled.
     * @return bool Returns true if the cancellation process is successful, otherwise false.
     */
    private function processCancellation($request, $params, $workers)
    {
        $fileName = $fileUrl = '';
        if (request()->hasFile('attachment')) {
            foreach ($request->file('attachment') as $file) {
                $fileName = $file->getClientOriginalName();
                $filePath = '/directRecruitment/workers/cancellation/' . $fileName;
                $linode = $this->storage::disk('linode');
                $linode->put($filePath, file_get_contents($file));
                $fileUrl = $this->storage::disk('linode')->url($filePath);
            }
        }
        $this->workerArrival
            ->whereIn('worker_id', $workers)
            ->where('arrival_id', $request['arrival_id'])
            ->update(
                ['arrival_status' => 'Cancelled',
                    'updated_at' => Carbon::now(),
                    'modified_by' => $params['created_by']]);
        $this->workers->whereIn('id', $workers)
            ->update([
                'directrecruitment_status' => 'Cancelled',
                'cancel_status' => 1,
                'remarks' => $request['remarks'] ?? '',
                'modified_by' => $params['created_by']
            ]);
        $arrivalDetails = $this->directrecruitmentArrival->findOrFail($request['arrival_id']);
        $workerDetails = [];
        // update utilised quota based on ksm reference number
        foreach ($workers as $worker) {
            $ksmDetails = $this->workerVisa->where('worker_id', $worker)->first(['ksm_reference_number']);
            $workerDetails[$worker] = $ksmDetails->ksm_reference_number;
        }
        $ksmCount = array_count_values($workerDetails);
        foreach ($ksmCount as $key => $value) {
            event(new KSMQuotaUpdated($arrivalDetails->onboarding_country_id, $key, $value, 'decrement'));
        }
        // update utilised quota in onboarding country
        event(new WorkerQuotaUpdated($arrivalDetails->onboarding_country_id, count($workers), 'decrement'));
        foreach ($workers as $workerId) {
            if (!empty($fileName) && !empty($fileUrl)) {
                $this->cancellationAttachment->updateOrCreate(
                    ['file_id' => $workerId],
                    ["file_name" => $fileName,
                        "file_type" => 'Arrival Cancellation Letter',
                        "file_url" => $fileUrl,
                        "remarks" => $request['remarks'] ?? ''
                    ]);
            }
        }

        return true;
    }

    /**
     * Cancel worker detail.
     *
     * @param array $request The request data containing 'company_id' and 'worker_id'.
     * @return Collection
     */
    public function cancelWorkerDetail($request): mixed
    {
        return $this->cancellationAttachment
            ->join('workers', 'workers.id', 'cancellation_attachment.file_id')
            ->where('workers.company_id', $request['company_id'])
            ->where('cancellation_attachment.file_id', $request['worker_id'])
            ->where('cancellation_attachment.file_type', 'Arrival Cancellation Letter')
            ->orWhere('cancellation_attachment.file_type', 'Cancellation Letter')
            ->select('cancellation_attachment.file_id', 'cancellation_attachment.file_name', 'cancellation_attachment.file_url', 'cancellation_attachment.remarks')
            ->get();
    }

    /**
     * Retrieve the list of calling visa reference numbers based on the given request.
     *
     * @param array $request The request parameters for filtering the data.
     *                      The array should contain the following keys:
     *                      - company_id: An array of company IDs to filter the workers.
     *                      - user: An array containing the information of the user initiating the request.
     *                              The array should contain the following keys:
     *                              -- user_type: The type of the user (e.g., "Customer").
     *                              -- reference_id: The ID of the user (e.g., CRM prospect ID).
     *                      - application_id: The ID of the application for filtering the workers.
     *                      - onboarding_country_id: The ID of the onboarding country for filtering the workers.
     * @return mixed The list of calling visa reference numbers. The returned value type is dependent on the ORM used.
     *               It could be an array, collection, or other data type supported by the ORM.
     */
    public function callingvisaReferenceNumberList($request): mixed
    {
        return $this->workers
            ->join('worker_visa', function ($join) {
                $join->on('worker_visa.worker_id', '=', 'workers.id')
                    ->where('worker_visa.approval_status', '=', 'Approved');
            })
            ->leftjoin('directrecruitment_workers', 'directrecruitment_workers.worker_id', '=', 'workers.id')
            ->whereIn('workers.company_id', $request['company_id'])
            ->where(function ($query) use ($request) {
                if ($request['user']['user_type'] == 'Customer') {
                    $query->where('workers.crm_prospect_id', '=', $request['user']['reference_id']);
                }
            })
            ->where([
                ['directrecruitment_workers.application_id', $request['application_id']],
                ['directrecruitment_workers.onboarding_country_id', $request['onboarding_country_id']]
            ])
            ->select('worker_visa.calling_visa_reference_number')
            ->distinct('worker_visa.calling_visa_reference_number')
            ->get();
    }

    /**
     * Update workers.
     *
     * @param array $request The request data containing workers information.
     *
     * @return bool|array Returns false if request does not contain any workers,
     *                   otherwise returns the result of the workers update process.
     */
    public function updateWorkers(array $request): bool|array
    {
        // Input validation
        $validator = $this->validateWorkerUpdateInputs($request);
        if ($validator->fails()) {
            return ['error' => $validator->errors()];
        }

        if (empty($request['workers'])) {
            return false;
        }

        // Workers update.
        return $this->processWorkersUpdate($request);
    }

    /**
     * Process the update of workers.
     *
     * @param array $request The request data containing the workers and other necessary information.
     * @return bool|array Returns true if the update is successful, otherwise returns an array with details of any errors encountered.
     */
    private function processWorkersUpdate(array $request): bool|array
    {
        $workerCompanyCount = $this->workers->whereIn('id', $request['workers'])
            ->where('company_id', $request['company_id'])
            ->count();
        if ($workerCompanyCount != count($request['workers'])) {
            return ['InvalidUser' => true];
        }

        $applicationCheck = $this->checkForArrivalApplication($request['company_id'], $request['arrival_id']);
        if (is_null($applicationCheck)) {
            return ['InvalidUser' => true];
        }

        try {
            foreach ($request['workers'] as $workerId) {
                $this->workerArrival->updateOrCreate(
                    [
                        'worker_id' => $workerId,
                        'arrival_id' => $request['arrival_id']
                    ],
                    [
                        'arrival_status' => $request['status'] ?? 'Not Arrived',
                        'created_by' => $request['modified_by'] ?? 0,
                        'modified_by' => $request['modified_by'] ?? 0
                    ]);
            }
        } catch (Exception $e) {
            // could handle/rethrow/log exception here
            return ['error' => $e->getMessage()];
        }

        return true;
    }

    /**
     * Validate the worker update inputs.
     *
     * @param array $request The array of request data to be validated.
     * @return \Illuminate\Contracts\Validation\Validator The validation instance.
     */
    private function validateWorkerUpdateInputs(array $request)
    {
        return Validator::make($request, $this->updateWorkersValidation());
    }

    /**
     * Retrieve the distinct flight dates for a specific application ID and onboarding country ID,
     * filtering by company ID.
     *
     * @param array $request The array containing the request data, including:
     *                      - company_id: The company ID
     *                      - application_id: The application ID
     *                      - onboarding_country_id: The onboarding country ID
     *
     * @return mixed Returns the distinct flight dates as a collection.
     */
    public function arrivalDateDropDown($request): mixed
    {
        return $this->directrecruitmentArrival
            ->join('directrecruitment_applications', function ($join) use ($request) {
                $join->on('directrecruitment_arrival.application_id', '=', 'directrecruitment_applications.id')
                    ->whereIn('directrecruitment_applications.company_id', $request['company_id']);
            })
            ->where('directrecruitment_arrival.application_id', $request['application_id'])
            ->where('directrecruitment_arrival.onboarding_country_id', $request['onboarding_country_id'])
            ->select('directrecruitment_arrival.flight_date')
            ->distinct()
            ->get();
    }

    /**
     * Checks for the application of onboarding country for a company
     *
     * @param int $companyId The ID of the company
     * @param int $onboardingCountryId
     * @return mixed Returns the onboarding country application data or null if not found
     */
    private function checkForOnboardingCountryApplication(int $companyId, int $onboardingCountryId): mixed
    {
        return $this->directRecruitmentOnboardingCountry
            ->join('directrecruitment_applications', function ($join) use ($companyId) {
                $join->on('directrecruitment_onboarding_countries.application_id', '=', 'directrecruitment_applications.id')
                    ->where('directrecruitment_applications.company_id', $companyId);
            })->select('directrecruitment_onboarding_countries.id', 'directrecruitment_onboarding_countries.application_id', 'directrecruitment_onboarding_countries.country_id', 'directrecruitment_onboarding_countries.quota', 'directrecruitment_onboarding_countries.utilised_quota', 'directrecruitment_onboarding_countries.status', 'directrecruitment_onboarding_countries.onboarding_status', 'directrecruitment_onboarding_countries.created_by', 'directrecruitment_onboarding_countries.modified_by', 'directrecruitment_onboarding_countries.created_at', 'directrecruitment_onboarding_countries.updated_at', 'directrecruitment_onboarding_countries.deleted_at')
            ->find($onboardingCountryId);
    }

    /**
     * Check for arrival application in the direct recruitment.
     *
     * @param int $companyId The company ID.
     * @param int $arrivalId The arrival ID.
     * @return mixed The instance of the found arrival application or null if not found.
     */
    private function checkForArrivalApplication(int $companyId, int $arrivalId): mixed
    {
        return $this->directrecruitmentArrival
            ->join('directrecruitment_applications', function ($join) use ($companyId) {
                $join->on('directrecruitment_arrival.application_id', '=', 'directrecruitment_applications.id')
                    ->where('directrecruitment_applications.company_id', $companyId);
            })->select('directrecruitment_arrival.id', 'directrecruitment_arrival.application_id', 'directrecruitment_arrival.onboarding_country_id', 'directrecruitment_arrival.item_name', 'directrecruitment_arrival.flight_date', 'directrecruitment_arrival.arrival_time', 'directrecruitment_arrival.flight_number', 'directrecruitment_arrival.status', 'directrecruitment_arrival.remarks', 'directrecruitment_arrival.created_by', 'directrecruitment_arrival.modified_by', 'directrecruitment_arrival.created_at', 'directrecruitment_arrival.updated_at', 'directrecruitment_arrival.deleted_at')->find($arrivalId);
    }
}
