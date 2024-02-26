<?php

namespace App\Services;

use App\Models\DirectRecruitmentPostArrivalStatus;
use App\Models\WorkerPLKSAttachments;
use App\Models\Workers;
use App\Models\DirectrecruitmentApplications;
use App\Models\DirectRecruitmentOnboardingCountry;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class DirectRecruitmentPostArrivalPLKSServices
{
    /**
     * @var DirectrecruitmentApplications
     */
    private DirectrecruitmentApplications $directrecruitmentApplications;
    /**
     * @var DirectRecruitmentPostArrivalStatus
     */
    private DirectRecruitmentPostArrivalStatus $directRecruitmentPostArrivalStatus;
    /**
     * @var WorkerPLKSAttachments
     */
    private WorkerPLKSAttachments $workerPLKSAttachments;
    /**
     * @var workers
     */
    private Workers $workers;
    /**
     * @var Storage
     */
    private Storage $storage;
    /**
     * @var DirectRecruitmentOnboardingCountry
     */
    private DirectRecruitmentOnboardingCountry $directRecruitmentOnboardingCountry;

    /**
     * Constructor method for the class.
     *
     * @param DirectrecruitmentApplications $directrecruitmentApplications The instance of the DirectrecruitmentApplications class.
     * @param DirectRecruitmentPostArrivalStatus $directRecruitmentPostArrivalStatus The instance of the DirectRecruitmentPostArrivalStatus class.
     * @param WorkerPLKSAttachments $workerPLKSAttachments The instance of the WorkerPLKSAttachments class.
     * @param Workers $workers The instance of the Workers class.
     * @param Storage $storage The instance of the Storage class.
     * @param DirectRecruitmentOnboardingCountry $directRecruitmentOnboardingCountry The instance of the DirectRecruitmentOnboardingCountry class.
     */
    public function __construct(
        DirectrecruitmentApplications      $directrecruitmentApplications,
        DirectRecruitmentPostArrivalStatus $directRecruitmentPostArrivalStatus,
        WorkerPLKSAttachments              $workerPLKSAttachments,
        Workers                            $workers,
        Storage                            $storage,
        DirectRecruitmentOnboardingCountry $directRecruitmentOnboardingCountry
    )
    {
        $this->directrecruitmentApplications = $directrecruitmentApplications;
        $this->directRecruitmentPostArrivalStatus = $directRecruitmentPostArrivalStatus;
        $this->workerPLKSAttachments = $workerPLKSAttachments;
        $this->workers = $workers;
        $this->storage = $storage;
        $this->directRecruitmentOnboardingCountry = $directRecruitmentOnboardingCountry;
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
    public function createValidation(): array
    {
        return [
            'plks_expiry_date' => 'required|date|date_format:Y-m-d|after:yesterday',
            'attachment.*' => 'mimes:jpeg,pdf,png|max:2048'
        ];
    }

    /**
     * Update the post arrival status for a specific application and onboarding country.
     *
     * @param int $applicationId The ID of the application.
     * @param int $onboardingCountryId The ID of the onboarding country.
     * @param int $modifiedBy The ID of the user who modified the status.
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
     * Retrieve a list of workers based on the given request parameters.
     *
     * @param array $request The request parameters.
     *                      The structure of the $request should be:
     *                      - company_id: array [required] The array of company ids.
     *                      - user: array [required] The details of the user making the request.
     *                         The structure of the 'user' array should be:
     *                         - user_type: string [required] The type of user.
     *                         - reference_id: mixed [required] The reference id of the user.
     *                      - application_id: mixed [required] The application id.
     *                      - onboarding_country_id: mixed [required] The onboarding country id.
     *                      - search: string The search keyword to filter the results. Default: empty string.
     *
     * @return mixed Returns the paginated list of workers.
     */
    public function workersList($request): mixed
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
            ->whereIn('workers.company_id', $request['company_id'])
            ->where(function ($query) use ($request) {
                if ($request['user']['user_type'] == 'Customer') {
                    $query->where('workers.crm_prospect_id', '=', $request['user']['reference_id']);
                }
            })
            ->where([
                'directrecruitment_workers.application_id' => $request['application_id'],
                'directrecruitment_workers.onboarding_country_id' => $request['onboarding_country_id'],
                'worker_fomema.fomema_status' => 'Fit'
            ])
            ->whereIn('workers.special_pass', [0, 2])
            ->where(function ($query) use ($request) {
                if (!empty($request['search'])) {
                    $query->where('workers.name', 'like', '%' . $request['search'] . '%')
                        ->orWhere('worker_visa.ksm_reference_number', 'like', '%' . $request['search'] . '%')
                        ->orWhere('workers.passport_number', 'like', '%' . $request['search'] . '%');
                }
            })
            ->select('workers.id', 'directrecruitment_workers.application_id', 'directrecruitment_workers.onboarding_country_id', 'workers.name', 'worker_visa.ksm_reference_number', 'worker_visa.calling_visa_reference_number', 'workers.passport_number', 'worker_visa.entry_visa_valid_until', 'workers.fomema_valid_until', 'workers.special_pass_valid_until', 'workers.plks_status')->distinct('workers.id')
            ->orderBy('workers.id', 'desc')
            ->paginate(Config::get('services.paginate_worker_row'));
    }

    /**
     * Update PLKS.
     *
     * @param array $request The request data.
     *
     * @return bool|array Returns true on success, or an array of validation errors on failure.
     */
    public function updatePLKS($request)
    {
        $validationResult = $this->validateRequest($request);

        if ($validationResult !== true) {
            return $validationResult;
        }

        if (!empty($request['workers'])) {
            $validationResult = $this->validateWorkers($request);

            if ($validationResult !== true) {
                return $validationResult;
            }

            $this->updateWorkers($request);
        }

        $this->updateDirectRecruitmentApplication($request);

        $this->updatePostArrivalStatus(
            $request['application_id'],
            $request['onboarding_country_id'],
            $request['modified_by']
        );

        return true;
    }

    /**
     * Validate the request.
     *
     * @param mixed $request The request object to validate.
     *
     * @return array|bool If validation fails, return an array containing the error messages. If validation passes, return true.
     */
    private function validateRequest($request): array|bool
    {
        $validator = Validator::make($request->toArray(), $this->createValidation());

        return $validator->fails()
            ? ['error' => $validator->errors()]
            : true;
    }

    /**
     * Validate the specified workers.
     *
     * @param array $request The request data containing the workers' information.
     *                      It should have the following keys:
     *                      - workers: A comma-separated string of worker IDs.
     *                      - company_id: The ID of the company.
     *                      - onboarding_country_id: The ID of the country for onboarding.
     *                      - application_id: The ID of the application.
     *
     * @return array|bool Returns an array with the key 'InvalidUser' set to true if the validation fails.
     *                   Returns true if the validation succeeds.
     */
    private function validateWorkers($request): array|bool
    {
        $request['workers'] = explode(',', $request['workers']);

        $workerCompanyCount = $this->workers
            ->whereIn('id', $request['workers'])
            ->where('company_id', $request['company_id'])
            ->count();

        if ($workerCompanyCount != count($request['workers'])) {
            return ['InvalidUser' => true];
        }

        $applicationCheck = $this->checkForApplication(
            $request['company_id'],
            $request['onboarding_country_id']
        );

        if (is_null($applicationCheck) ||
            $applicationCheck->application_id != $request['application_id']) {
            return ['InvalidUser' => true];
        }

        return true;
    }

    /**
     * Update the workers statuses and details.
     *
     * @param array $request The request data.
     *
     * @return void
     */
    private function updateWorkers($request): void
    {
        $this->workers->whereIn('id', $request['workers'])
            ->update([
                'directrecruitment_status' => 'Processed',
                'plks_status' => 'Approved',
                'plks_expiry_date' => $request['plks_expiry_date'],
                'modified_by' => $request['modified_by'],
            ]);

        if (request()->hasFile('attachment')) {
            $this->handleAttachments($request);
        }
    }

    /**
     * Handle attachments for workers
     *
     * @param array $request The request data containing attachments
     *
     * @return void
     */
    private function handleAttachments($request): void
    {
        foreach ($request['workers'] as $workerId) {
            foreach ($request->file('attachment') as $file) {
                $fileName = $file->getClientOriginalName();
                $filePath = 'directRecruitment/workers/plks/' . $workerId . '/' . $fileName;
                $linode = $this->storage::disk('linode');

                $linode->put($filePath, file_get_contents($file));

                $fileUrl = $linode->url($filePath);

                $this->workerPLKSAttachments->create([
                    'file_id' => $workerId,
                    'file_name' => $fileName,
                    'file_type' => 'PLKS',
                    'file_url' => $fileUrl,
                    'created_by' => $request['modified_by'],
                    'modified_by' => $request['modified_by'],
                ]);
            }
        }
    }

    /**
     * Update direct recruitment application onboarding flag.
     *
     * @param array $request The request data containing the application ID.
     *
     * @return void
     */
    private function updateDirectRecruitmentApplication($request): void
    {
        $this->directrecruitmentApplications
            ->where('id', $request['application_id'])
            ->update(['onboarding_flag' => 1]);
    }

    /**
     * Exports a list of workers based on the provided request.
     *
     * @param array $request The request containing search filters and other parameters.
     *      Example:
     *      [
     *          'search' => 'John',
     *          'company_id' => [1, 2, 3],
     *          'user' => [
     *              'user_type' => 'Customer',
     *              'reference_id' => 123
     *          ],
     *          'application_id' => 456,
     *          'onboarding_country_id' => 789
     *      ]
     * @return Collection|array If the validation fails, it returns an array with an 'error' key
     *      containing the validation errors. Otherwise, it returns a collection of workers with their details.
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
        return $this->workers
            ->leftJoin('worker_visa', 'worker_visa.worker_id', 'workers.id')
            ->leftJoin('worker_fomema', 'worker_fomema.worker_id', 'workers.id')
            ->leftjoin('directrecruitment_workers', 'directrecruitment_workers.worker_id', '=', 'workers.id')
            ->whereIn('workers.company_id', $request['company_id'])
            ->where(function ($query) use ($request) {
                if ($request['user']['user_type'] == 'Customer') {
                    $query->where('workers.crm_prospect_id', '=', $request['user']['reference_id']);
                }
            })
            ->where([
                'directrecruitment_workers.application_id' => $request['application_id'],
                'directrecruitment_workers.onboarding_country_id' => $request['onboarding_country_id'],
                'worker_fomema.fomema_status' => 'Fit'
            ])
            ->where(function ($query) use ($request) {
                if (!empty($request['search'])) {
                    $query->where('workers.name', 'like', '%' . $request['search'] . '%')
                        ->orWhere('worker_visa.ksm_reference_number', 'like', '%' . $request['search'] . '%')
                        ->orWhere('workers.passport_number', 'like', '%' . $request['search'] . '%');
                }
            })
            ->select('workers.id', 'directrecruitment_workers.application_id', 'directrecruitment_workers.onboarding_country_id', 'workers.name', 'worker_visa.ksm_reference_number', 'worker_visa.calling_visa_reference_number', 'workers.passport_number', 'worker_visa.entry_visa_valid_until', 'workers.fomema_valid_until', 'workers.special_pass_valid_until', 'workers.plks_status')->distinct('workers.id')
            ->orderBy('workers.id', 'desc')
            ->get();
    }

    /**
     * Check if an application exists for a specific company and onboarding country.
     *
     * @param int $companyId The ID of the company.
     * @param int $onboardingCountryId The ID of the onboarding country.
     * @return mixed Returns the found onboarding country information if an application exists, otherwise returns null.
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
