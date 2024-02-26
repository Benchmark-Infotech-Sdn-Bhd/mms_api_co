<?php

namespace App\Services;

use App\Models\Workers;
use App\Models\WorkerArrival;
use App\Models\DirectrecruitmentWorkers;
use App\Models\DirectRecruitmentOnboardingCountry;
use App\Models\WorkerVisa;
use App\Models\OnboardingCountriesKSMReferenceNumber;
use App\Models\CallingVisaExpiryCronDetails;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class DirectRecruitmentPostponedServices
{

    public const USER_TYPE = 'Customer';
    public const REQUEST_COMPANY_ID = 'company_id';
    public const REQUEST_ONBOARDING_COUNTRY_ID = 'onboarding_country_id';
    public const REQUEST_KSM_REFERENCE_NUMBER = 'ksm_reference_number';

    public const ARRIVAL_STATUS_POSTPONED = 'Postponed';
    public const ARRIVAL_STATUS_NOT_ARRIVED = 'Not Arrived';
    public const VISA_STATUS = 'Expired';
    public const STATUS_EXPIRED = 'Expired';

    public const DEFAULT_INT_VALUE = 0;

    /**
     * @var workers
     */
    private Workers $workers;
    /**
     * @var WorkerArrival
     */
    private WorkerArrival $workerArrival;
    /**
     * @var DirectrecruitmentWorkers
     */
    private DirectrecruitmentWorkers $directrecruitmentWorkers;
    /**
     * @var DirectRecruitmentOnboardingCountry
     */
    private DirectRecruitmentOnboardingCountry $directRecruitmentOnboardingCountry;
    /**
     * @var WorkerVisa
     */
    private WorkerVisa $workerVisa;
    /**
     * @var OnboardingCountriesKSMReferenceNumber
     */
    private OnboardingCountriesKSMReferenceNumber $onboardingCountriesKSMReferenceNumber;
    /**
     * @var CallingVisaExpiryCronDetails
     */
    private CallingVisaExpiryCronDetails $callingVisaExpiryCronDetails;

    /**
     * Constructor method for ClassName.
     *
     * @param Workers $workers An instance of the Workers class.
     * @param WorkerArrival $workerArrival An instance of the WorkerArrival class.
     * @param DirectrecruitmentWorkers $directrecruitmentWorkers An instance of the DirectrecruitmentWorkers class.
     * @param DirectRecruitmentOnboardingCountry $directRecruitmentOnboardingCountry An instance of the DirectRecruitmentOnboardingCountry class.
     * @param WorkerVisa $workerVisa An instance of the WorkerVisa class.
     * @param OnboardingCountriesKSMReferenceNumber $onboardingCountriesKSMReferenceNumber An instance of the OnboardingCountriesKSMReferenceNumber class.
     * @param CallingVisaExpiryCronDetails $callingVisaExpiryCronDetails An instance of the CallingVisaExpiryCronDetails class.
     * @return void
     */
    public function __construct(
        Workers                               $workers,
        WorkerArrival                         $workerArrival,
        DirectrecruitmentWorkers              $directrecruitmentWorkers,
        DirectRecruitmentOnboardingCountry    $directRecruitmentOnboardingCountry,
        WorkerVisa                            $workerVisa,
        OnboardingCountriesKSMReferenceNumber $onboardingCountriesKSMReferenceNumber,
        CallingVisaExpiryCronDetails          $callingVisaExpiryCronDetails
    )
    {
        $this->workers = $workers;
        $this->workerArrival = $workerArrival;
        $this->directrecruitmentWorkers = $directrecruitmentWorkers;
        $this->directRecruitmentOnboardingCountry = $directRecruitmentOnboardingCountry;
        $this->workerVisa = $workerVisa;
        $this->onboardingCountriesKSMReferenceNumber = $onboardingCountriesKSMReferenceNumber;
        $this->callingVisaExpiryCronDetails = $callingVisaExpiryCronDetails;
    }

    /**
     * Creates the validation rules for search .
     *
     * @return array The array containing the validation rules.
     */
    public function searchValidation(): array
    {
        return [
            'search' => 'required|min:3'
        ];
    }

    /**
     * Retrieves a list of workers based on the provided request.
     *
     * @param array $request The array containing the request data.
     *   - search (string|null): The search keyword to filter the workers.
     *   - user (array): The user information.
     *       - user_type (string): The type of the user.
     *       - reference_id (int): The reference ID of the user.
     *   - {REQUEST_COMPANY_ID} (mixed): The company ID for filtering workers.
     *   - {REQUEST_ONBOARDING_COUNTRY_ID} (mixed): The onboarding country ID for filtering workers.
     *
     * @return LengthAwarePaginator|array The paginated list of workers.
     *   - items (array): The array containing the worker details.
     *       - id (int): The ID of the worker.
     *       - application_id (mixed): The ID of the application.
     *       - onboarding_country_id (mixed): The ID of the onboarding country.
     *       - name (string): The name of the worker.
     *       - ksm_reference_number (string|null): The KSM reference number of the worker's visa.
     *       - passport_number (string|null): The passport number of the worker.
     *       - flight_number (string|null): The flight number of the worker's arrival.
     *       - flight_date (string|null): The flight date of the worker's arrival.
     *       - arrival_time (string|null): The arrival time of the worker's arrival.
     *   - total (int): The total count of workers.
     *   - per_page (int): The number of workers per page.
     *   - current_page (int): The current page number.
     *   - last_page (int): The last page number.
     *
     * @throws ValidationException If the validation fails.
     */
    public function workersList($request): LengthAwarePaginator|array
    {
        if (!empty($request['search'])) {
            $validator = Validator::make($request, $this->searchValidation());
            if ($validator->fails()) {
                return [
                    'error' => $validator->errors()
                ];
            }
        }
        return $this->workers
            ->leftJoin('worker_visa', 'worker_visa.worker_id', 'workers.id')
            ->leftJoin('worker_fomema', 'worker_fomema.worker_id', 'workers.id')
            ->leftjoin('directrecruitment_workers', 'directrecruitment_workers.worker_id', '=', 'workers.id')
            ->leftjoin('worker_arrival', 'worker_arrival.worker_id', '=', 'workers.id')
            ->leftjoin('directrecruitment_arrival', 'directrecruitment_arrival.id', '=', 'worker_arrival.arrival_id')
            ->whereIn('workers.company_id', $request[self::REQUEST_COMPANY_ID])
            ->where(function ($query) use ($request) {
                if ($request['user']['user_type'] == self::USER_TYPE) {
                    $query->where('workers.crm_prospect_id', '=', $request['user']['reference_id']);
                }
            })
            ->where([
                'directrecruitment_workers.application_id' => $request[self::REQUEST_COMPANY_ID],
                'directrecruitment_workers.onboarding_country_id' => $request[self::REQUEST_ONBOARDING_COUNTRY_ID],
                'worker_arrival.arrival_status' => self::ARRIVAL_STATUS_POSTPONED
            ])
            ->where(function ($query) use ($request) {
                if (!empty($request['search'])) {
                    $query->where('workers.name', 'like', '%' . $request['search'] . '%')
                        ->orWhere('worker_visa.ksm_reference_number', 'like', '%' . $request['search'] . '%')
                        ->orWhere('workers.passport_number', 'like', '%' . $request['search'] . '%');
                }
            })
            ->select('workers.id', 'directrecruitment_workers.application_id', 'directrecruitment_workers.onboarding_country_id', 'workers.name', 'worker_visa.ksm_reference_number', 'workers.passport_number', 'directrecruitment_arrival.flight_number', 'directrecruitment_arrival.flight_date', 'directrecruitment_arrival.arrival_time')
            ->distinct('workers.id')
            ->orderBy('workers.id', 'desc')
            ->paginate(Config::get('services.paginate_worker_row'));
    }

    /**
     * Retrieves the IDs of workers who have a postponed arrival status and an expired calling visa.
     *
     * @return array An array containing the IDs of the postponed workers.
     */
    public function getPostponedWorkerIds(): array
    {
        $postponedWorkerIds = $this->workerArrival
            ->leftJoin('worker_visa', 'worker_visa.worker_id', 'worker_arrival.worker_id')
            ->where('worker_arrival.arrival_status', self::ARRIVAL_STATUS_POSTPONED)
            ->where('worker_visa.calling_visa_valid_until', '<', Carbon::now()->format('Y-m-d'))
            ->where('worker_visa.status', '<>', self::VISA_STATUS)
            ->select('worker_arrival.worker_id')
            ->distinct('worker_arrival.worker_id')
            ->get()->toArray();
        return array_column($postponedWorkerIds, 'worker_id');
    }

    /**
     * Gets the worker IDs of postponed workers based on given worker IDs.
     *
     * @param array $postponedWorkerIds The array of worker IDs to search for.
     * @return array The array of worker IDs found.
     */
    public function getWorkerIds($postponedWorkerIds): array
    {
        $workerIds = $this->workerArrival
            ->whereIn('worker_id', $postponedWorkerIds)
            ->where('arrival_status', self::ARRIVAL_STATUS_NOT_ARRIVED)
            ->select('worker_id')
            ->distinct('worker_id')
            ->get()->toArray();
        return array_column($workerIds, 'worker_id');
    }

    /**
     * Updates the calling visa expiry for postponed workers.
     *
     * @return bool Returns true if the update was successful, otherwise false.
     */
    public function updateCallingVisaExpiry(): bool
    {
        $postponedWorkerIds = $this->getPostponedWorkerIds();
        if (empty($postponedWorkerIds)) {
            return true;
        }

        $workerDetails = $this->getWorkerDetails($postponedWorkerIds);
        $this->updateCronTableInitialQuota($workerDetails);
        $this->updateWorkerAndVisaStatus($workerDetails);
        $this->updateCountryAndKsmQuota($workerDetails);
        $this->updateCronTableCurrentQuota($workerDetails);

        return true;
    }

    /**
     * Retrieves the details of workers based on the given postponed worker IDs.
     *
     * @param array $postponedWorkerIds An array containing the IDs of postponed workers.
     * @return array The array containing the worker details in the following format:
     *         [
     *             worker_id => [
     *                 'onboarding_country_id' => The onboarding country ID of the worker,
     *                 onboarding_country_id => [
     *                     ksm_reference_number => [
     *                         'onboarding_country_id' => The onboarding country ID of the worker,
     *                         'company_id' => The application ID of the worker
     *                     ]
     *                 ]
     *             ]
     *         ]
     */
    protected function getWorkerDetails($postponedWorkerIds): array
    {
        $workerIds = $this->getWorkerIds($postponedWorkerIds);
        $workerDetails = [];
        foreach ($workerIds as $worker) {
            $ksmDetails = $this->workerVisa->where('worker_id', $worker)->first([self::REQUEST_KSM_REFERENCE_NUMBER]);
            $onboardingDetails = $this->directrecruitmentWorkers->where('worker_id', $worker)
                ->first(['application_id', 'onboarding_country_id']);
            $workerDetails[$worker]['onboarding_country_id'] = $onboardingDetails->onboarding_country_id;
            $workerDetails[$onboardingDetails->onboarding_country_id][$ksmDetails->ksm_reference_number]['onboarding_country_id'] = $onboardingDetails->onboarding_country_id;
            $workerDetails[$onboardingDetails->onboarding_country_id][$ksmDetails->ksm_reference_number][self::REQUEST_COMPANY_ID] = $onboardingDetails->application_id;
        }
        return $workerDetails;
    }

    /**
     * Update the cron table with initial quota details for worker.
     *
     * @param array $workerDetails The array containing worker details.
     * @return void
     */
    protected function updateCronTableInitialQuota($workerDetails): void
    {
        foreach ($workerDetails as $workerKSM) {
            foreach ($workerKSM as $ksmRefNum => $ksmDetail) {
                $quotaDetails = $this->onboardingCountriesKSMReferenceNumber->where('application_id', $ksmDetail[self::REQUEST_COMPANY_ID])
                    ->where('onboarding_country_id', $ksmDetail[self::REQUEST_ONBOARDING_COUNTRY_ID])
                    ->where('ksm_reference_number', $ksmRefNum)
                    ->first(['quota', 'utilised_quota']);
                $this->callingVisaExpiryCronDetails->create([
                    'application_id' => $ksmDetail[self::REQUEST_COMPANY_ID],
                    'onboarding_country_id' => $ksmDetail[self::REQUEST_ONBOARDING_COUNTRY_ID],
                    'ksm_reference_number' => $ksmRefNum,
                    'approved_quota' => $quotaDetails->quota,
                    'initial_utilised_quota' => $quotaDetails->utilised_quota,
                    'current_utilised_quota' => self::DEFAULT_INT_VALUE,
                ]);
            }
        }
    }

    /**
     * Updates the worker status and visa status.
     *
     * @param array $workerDetails The array containing the worker details.
     *                             It should have the following structure:
     *                             [
     *                                 'worker_id' => 'worker_details',
     *                                 ...
     *                             ];
     *                             where 'worker_id' is the ID of the worker and
     *                             'worker_details' is an array containing the worker details.
     *
     * @return void
     */
    protected function updateWorkerAndVisaStatus($workerDetails): void
    {
        $workerIds = array_keys($workerDetails);
        $this->workers->whereIn('id', $workerIds)
            ->update(['directrecruitment_status' => self::STATUS_EXPIRED]);
        $this->workerVisa->whereIn('worker_id', $workerIds)
            ->update(['status' => self::STATUS_EXPIRED]);
    }

    /**
     * Updates the country and KSM quota based on the worker details.
     *
     * @param array $workerDetails The array of worker details.
     * @return void
     */
    protected function updateCountryAndKsmQuota($workerDetails): void
    {
        foreach ($workerDetails as $workerId => $workerDetail) {
            $countryDetails = $this->directRecruitmentOnboardingCountry->findOrFail($workerDetail[self::REQUEST_ONBOARDING_COUNTRY_ID]);
            $utilisedQuota = (($countryDetails->utilised_quota - 1) < self::DEFAULT_INT_VALUE) ? self::DEFAULT_INT_VALUE : $countryDetails->utilised_quota - 1;
            $countryDetails->utilised_quota = $utilisedQuota;
            $countryDetails->save();

            // updating quota based on ksm reference number
            $WorkerKSMDetails = $this->workerVisa->where('worker_id', $workerId)->first([self::REQUEST_KSM_REFERENCE_NUMBER]);
            $ksmDetails = $this->onboardingCountriesKSMReferenceNumber->where('onboarding_country_id', $countryDetails->id)
                ->where('ksm_reference_number', $WorkerKSMDetails->ksm_reference_number)
                ->first(['id', 'utilised_quota']);
            $ksmUtilisedQuota = (($ksmDetails->utilised_quota - 1) < self::DEFAULT_INT_VALUE) ? self::DEFAULT_INT_VALUE : $ksmDetails->utilised_quota - 1;
            $this->onboardingCountriesKSMReferenceNumber->where('id', $ksmDetails->id)->update(['utilised_quota' => $ksmUtilisedQuota]);
        }
    }

    /**
     * Updates the current quota in the cron table based on worker details.
     *
     * @param array $workerDetails The worker details array.
     * @return void
     */
    protected function updateCronTableCurrentQuota($workerDetails): void
    {
        foreach ($workerDetails as $workerKSM) {
            foreach ($workerKSM as $ksmRefNum => $ksmDetail) {
                $currentQuotaDetails = $this->onboardingCountriesKSMReferenceNumber->where('application_id', $ksmDetail[self::REQUEST_COMPANY_ID])
                    ->where('onboarding_country_id', $ksmDetail[self::REQUEST_ONBOARDING_COUNTRY_ID])
                    ->where('ksm_reference_number', $ksmRefNum)
                    ->first(['quota', 'utilised_quota']);
                $this->callingVisaExpiryCronDetails->where([
                    ['onboarding_country_id', $ksmDetail[self::REQUEST_ONBOARDING_COUNTRY_ID]],
                    ['application_id', $ksmDetail[self::REQUEST_COMPANY_ID]],
                    ['ksm_reference_number', $ksmRefNum]
                ])->update(['current_utilised_quota' => $currentQuotaDetails->utilised_quota]);
            }
        }
    }
}
