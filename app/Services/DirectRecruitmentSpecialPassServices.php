<?php

namespace App\Services;

use App\Models\DirectRecruitmentPostArrivalStatus;
use App\Models\SpecialPassAttachments;
use App\Models\Workers;
use App\Models\DirectRecruitmentOnboardingCountry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class DirectRecruitmentSpecialPassServices
{
    /**
     * @var DirectRecruitmentPostArrivalStatus
     */
    private DirectRecruitmentPostArrivalStatus $directRecruitmentPostArrivalStatus;
    /**
     * @var SpecialPassAttachments
     */
    private SpecialPassAttachments $specialPassAttachments;
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
     * DirectRecruitmentPostArrivalFomemaServices constructor.
     * @param DirectRecruitmentPostArrivalStatus $directRecruitmentPostArrivalStatus
     * @param SpecialPassAttachments $specialPassAttachments
     * @param Workers $workers
     * @param Storage $storage
     * @param DirectRecruitmentOnboardingCountry $directRecruitmentOnboardingCountry
     */
    public function __construct(
        DirectRecruitmentPostArrivalStatus $directRecruitmentPostArrivalStatus,
        SpecialPassAttachments             $specialPassAttachments,
        Workers                            $workers,
        Storage                            $storage,
        DirectRecruitmentOnboardingCountry $directRecruitmentOnboardingCountry
    )
    {
        $this->directRecruitmentPostArrivalStatus = $directRecruitmentPostArrivalStatus;
        $this->specialPassAttachments = $specialPassAttachments;
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
    public function submissionValidation(): array
    {
        return [
            'submission_date' => 'required|date|date_format:Y-m-d|before:tomorrow'
        ];
    }

    /**
     * @return array
     */
    public function validityValidation(): array
    {
        return [
            'valid_until' => 'required|date|date_format:Y-m-d|after:yesterday'
        ];
    }

    /**
     * Update the post arrival status of a specific application.
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
     * @param array $request The request parameters containing the following keys:
     *                      - search: (optional) The search keyword.
     *                      - company_id: The ID(s) of the company for filtering workers.
     *                      - user: The user information.
     *                      - application_id: The application ID for filtering workers.
     *                      - onboarding_country_id: The onboarding country ID for filtering workers.
     *
     * @return mixed Returns a paginated list of workers with the following fields:
     *               - id: The ID of the worker.
     *               - name: The name of the worker.
     *               - ksm_reference_number: The KSM reference number of the worker's visa.
     *               - passport_number: The passport number of the worker.
     *               - entry_visa_valid_until: The validity date of the entry visa.
     *               - application_id: The ID of the application.
     *               - onboarding_country_id: The ID of the onboarding country.
     *               - special_pass_submission_date: The submission date of the special pass.
     *               - special_pass_valid_until: The validity date of the special pass.
     *
     *               If the validation fails, it returns an array with an 'error' key containing the validation errors.
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
                'workers.special_pass' => 1
            ])
            ->where(function ($query) use ($request) {
                if (!empty($request['search'])) {
                    $query->where('workers.name', 'like', '%' . $request['search'] . '%')
                        ->orWhere('worker_visa.ksm_reference_number', 'like', '%' . $request['search'] . '%')
                        ->orWhere('workers.passport_number', 'like', '%' . $request['search'] . '%');
                }
            })
            ->select('workers.id', 'workers.name', 'worker_visa.ksm_reference_number', 'workers.passport_number', 'worker_visa.entry_visa_valid_until', 'directrecruitment_workers.application_id', 'directrecruitment_workers.onboarding_country_id', 'workers.special_pass_submission_date', 'workers.special_pass_valid_until')->distinct('workers.id')
            ->orderBy('workers.id', 'desc')
            ->paginate(Config::get('services.paginate_worker_row'));
    }

    /**
     * Update the submission with the given request.
     *
     * @param array $request The request data for update.
     *
     * @return bool|array True if the submission is successfully updated, otherwise validation error messages as an array.
     */
    public function updateSubmission($request)
    {
        $validationResult = $this->validateSubmission($request);
        if ($validationResult !== true) {
            return $validationResult;
        }

        if (!empty($request['workers'])) {
            $this->updateWorkersInfo($request);
            $this->handleAttachments($request);
        }

        $this->updatePostArrivalStatus(
            $request['application_id'],
            $request['onboarding_country_id'],
            $request['modified_by']
        );

        return true;
    }

    /**
     * Validates the submission request.
     *
     * @param $request - The submission request to be validated.
     * @return bool|array Returns true if the validation passes, or an array of error messages if validation fails.
     */
    private function validateSubmission($request): bool|array
    {
        $validator = Validator::make($request->toArray(), $this->submissionValidation());

        if ($validator->fails()) {
            return ['error' => $validator->errors()];
        }

        $applicationCheck = $this->checkForApplication($request['company_id'], $request['onboarding_country_id']);

        if (
            is_null($applicationCheck)
            || ($applicationCheck->application_id != $request['application_id'])
        ) {
            return ['InvalidUser' => true];
        }

        if (!empty($request['workers'])) {
            $request['workers'] = explode(',', $request['workers']);
            $workerCompanyCount = $this->workers->whereIn('id', $request['workers'])
                ->where('company_id', $request['company_id'])
                ->count();

            if ($workerCompanyCount != count($request['workers'])) {
                return ['InvalidUser' => true];
            }
        }

        return true;
    }

    /**
     * Update the information of workers.
     *
     * @param array $request An array containing the request data.
     *                      - workers (array): An array of worker IDs to update.
     *                      - submission_date (string): The special pass submission date.
     *                      - modified_by (string): The name of the modifier.
     *
     * @return void
     */
    private function updateWorkersInfo($request): void
    {
        $this->workers->whereIn('id', $request['workers'])->update([
            'special_pass_submission_date' => $request['submission_date'],
            'modified_by' => $request['modified_by']
        ]);
    }

    /**
     * Handle attachments uploaded by the user.
     *
     * @param $request - The incoming HTTP request.
     *
     * @return void
     */
    private function handleAttachments($request): void
    {
        if (request()->hasFile('attachment')) {
            foreach ($request->file('attachment') as $file) {
                $fileName = $file->getClientOriginalName();
                $filePath = 'directRecruitment/workers/specialPass/' . Carbon::now()->format('Ymd') . '/' . $fileName;
                $linode = $this->storage::disk('linode');
                $linode->put($filePath, file_get_contents($file));
                $fileUrl = $this->storage::disk('linode')->url($filePath);

                foreach ($request['workers'] as $workerId) {
                    $this->specialPassAttachments->create([
                        'file_id' => $workerId,
                        'file_name' => $fileName,
                        'file_type' => 'Special Pass Attachment',
                        'file_url' => $fileUrl,
                        'created_by' => $request['modified_by'],
                        'modified_by' => $request['modified_by']
                    ]);
                }
            }
        }
    }

    /**
     * Validate and update the validity of a request
     *
     * @param Request $request The request object containing the data to be validated and updated
     * @return array|bool If validation fails, an array containing the error messages. Otherwise, true is returned.
     */
    public function updateValidity($request)
    {
        $validator = Validator::make($request->toArray(), $this->validityValidation());
        if ($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }
        if (!empty($request['workers'])) {
            $this->processWorkersData($request);
        }
        $this->updatePostArrivalStatus($request['application_id'], $request['onboarding_country_id'], $request['modified_by']);
        return true;
    }

    /**
     * Process the workers' data.
     *
     * @param $request - The request data.
     * @return array|void Returns an array with different possible error messages or false if no errors.
     */
    private function processWorkersData($request)
    {
        $request['workers'] = explode(',', $request['workers']);
        $workerCompanyCount = $this->workers->whereIn('id', $request['workers'])
            ->where('company_id', $request['company_id'])
            ->count();
        if ($workerCompanyCount != count($request['workers'])) {
            return [
                'InvalidUser' => true
            ];
        }
        $applicationCheck = $this->checkForApplication($request['company_id'], $request['onboarding_country_id']);
        if (is_null($applicationCheck) || ($applicationCheck->application_id != $request['application_id'])) {
            return [
                'InvalidUser' => true
            ];
        }
        $submissionValidation = $this->workers
            ->where('special_pass_submission_date', NULL)
            ->whereIn('id', $request['workers'])
            ->count('id');
        if ($submissionValidation > 0) {
            return [
                'submissionError' => true
            ];
        }
        $this->updateWorkersStatus($request);
        if (request()->hasFile('attachment')) {
            $this->processFileAttachments($request);
        }
    }

    /**
     * Updates the status of workers.
     *
     * @param array $request The request data containing 'workers', 'valid_until', and 'modified_by' keys.
     * @return void
     */
    private function updateWorkersStatus($request): void
    {
        $this->workers->whereIn('id', $request['workers'])
            ->update([
                'special_pass' => 2,
                'special_pass_valid_until' => $request['valid_until'],
                'modified_by' => $request['modified_by']
            ]);
    }

    /**
     * Process file attachments.
     *
     * @param Request $request The request object.
     * @return void
     */
    private function processFileAttachments($request): void
    {
        foreach ($request->file('attachment') as $file) {
            $fileName = $file->getClientOriginalName();
            $filePath = 'directRecruitment/workers/specialPass/' . Carbon::now()->format('Ymd') . '/' . $fileName;
            $linode = $this->storage::disk('linode');
            $linode->put($filePath, file_get_contents($file));
            $fileUrl = $this->storage::disk('linode')->url($filePath);

            $this->createAttachments($request, $fileName, $fileUrl);
        }
    }

    /**
     * Create attachments for workers.
     *
     * @param $request - The request data that contains the workers array, modified_by value, etc.
     * @param string $fileName The name of the file being attached.
     * @param string $fileUrl The URL of the file being attached.
     * @return void
     */
    private function createAttachments($request, string $fileName, string $fileUrl): void
    {
        foreach ($request['workers'] as $workerId) {
            $this->specialPassAttachments->create([
                'file_id' => $workerId,
                'file_name' => $fileName,
                'file_type' => 'Special Pass Attachment',
                'file_url' => $fileUrl,
                'created_by' => $request['modified_by'],
                'modified_by' => $request['modified_by']
            ]);
        }
    }

    /**
     * Export the list of workers based on the given request data
     *
     * @param array $request The request data containing search parameters and company ID
     * @return mixed Returns the list of workers based on the request data
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
                'workers.special_pass' => 1
            ])
            ->where(function ($query) use ($request) {
                if (!empty($request['search'])) {
                    $query->where('workers.name', 'like', '%' . $request['search'] . '%')
                        ->orWhere('worker_visa.ksm_reference_number', 'like', '%' . $request['search'] . '%')
                        ->orWhere('workers.passport_number', 'like', '%' . $request['search'] . '%');
                }
            })
            ->select('workers.id', 'workers.name', 'worker_visa.ksm_reference_number', 'workers.passport_number', 'worker_visa.entry_visa_valid_until', 'directrecruitment_workers.application_id', 'directrecruitment_workers.onboarding_country_id', 'workers.special_pass_submission_date')->distinct('workers.id')
            ->orderBy('workers.id', 'desc')
            ->get();
    }

    /**
     * Check for the existence of an application for a given company and onboarding country.
     *
     * @param int $companyId The ID of the company
     * @param int $onboardingCountryId The ID of the onboarding country
     * @return mixed Returns a single record from the directrecruitment_onboarding_countries table
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
