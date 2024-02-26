<?php

namespace App\Services;

use App\Models\DirectRecruitmentCallingVisaStatus;
use App\Models\WorkerInsuranceDetails;
use App\Models\Workers;
use App\Models\WorkerVisa;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Events\WorkerQuotaUpdated;
use App\Events\KSMQuotaUpdated;
use App\Models\DirectRecruitmentOnboardingCountry;

class DirectRecruitmentCallingVisaApprovalServices
{
    /**
     * @var Workers
     */
    private Workers $workers;
    /**
     * @var WorkerVisa
     */
    private WorkerVisa $workerVisa;
    /**
     * @var workerInsuranceDetails
     */
    private WorkerInsuranceDetails $workerInsuranceDetails;
    /**
     * @var DirectRecruitmentCallingVisaStatus
     */
    private DirectRecruitmentCallingVisaStatus $directRecruitmentCallingVisaStatus;
    /**
     * @var DirectRecruitmentOnboardingCountry
     */
    private DirectRecruitmentOnboardingCountry $directRecruitmentOnboardingCountry;

    /**
     * Create a new instance of the class.
     *
     * @param Workers $workers The instance of Workers class.
     * @param WorkerVisa $workerVisa The instance of WorkerVisa class.
     * @param WorkerInsuranceDetails $workerInsuranceDetails The instance of WorkerInsuranceDetails class.
     * @param DirectRecruitmentCallingVisaStatus $directRecruitmentCallingVisaStatus The instance of DirectRecruitmentCallingVisaStatus class.
     * @param DirectRecruitmentOnboardingCountry $directRecruitmentOnboardingCountry The instance of DirectRecruitmentOnboardingCountry class.
     *
     * @return void
     */
    public function __construct(
        Workers                            $workers,
        WorkerVisa                         $workerVisa,
        WorkerInsuranceDetails             $workerInsuranceDetails,
        DirectRecruitmentCallingVisaStatus $directRecruitmentCallingVisaStatus,
        DirectRecruitmentOnboardingCountry $directRecruitmentOnboardingCountry
    )
    {
        $this->workers = $workers;
        $this->workerVisa = $workerVisa;
        $this->workerInsuranceDetails = $workerInsuranceDetails;
        $this->directRecruitmentCallingVisaStatus = $directRecruitmentCallingVisaStatus;
        $this->directRecruitmentOnboardingCountry = $directRecruitmentOnboardingCountry;
    }

    /**
     * @return array
     */
    public function createValidation(): array
    {
        return [
            'calling_visa_generated' => 'required|date|date_format:Y-m-d|before:tomorrow',
            'calling_visa_valid_until' => 'required|date|date_format:Y-m-d|after:today'
        ];
    }

    /**
     * @return array
     */
    public function statusValidation(): array
    {
        return [
            'status' => 'required'
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
     * Update the approval status of a request.
     *
     * @param array $request The request data.
     * @return bool|array Returns true if the approval was successful, otherwise an array of errors.
     */
    public function approvalStatusUpdate($request): bool|array
    {
        $validationResult = $this->validateStatus($request);
        if ($validationResult) {
            return $validationResult;
        }

        if ($this->isWorkerDataPresent($request)) {
            $errors = $this->carryOutWorkerChecks($request);
            if ($errors) {
                return $errors;
            }
        }

        if ($request['status'] == 'Approved') {
            $approval = $this->approveRequest($request);
            if ($approval) {
                return $approval;
            }
        } else {
            $this->rejectRequest($request);
        }

        $this->updateCallingVisaStatus($request);

        return true;
    }

    /**
     * Rejects a request.
     *
     * This method updates the approval_status and modified_by fields in the worker_visa table and
     * the directrecruitment_status and modified_by fields in the workers table for specific workers.
     *
     * @param array $request An array containing the following keys:
     *                       - workers: An array of worker IDs to reject.
     *                       - status: The new approval status to set for the workers.
     *                       - modified_by: The user ID of the modifier.
     *
     * @return void
     */
    public function rejectRequest($request): void
    {
        $this->workerVisa->whereIn('worker_id', $request['workers'])
            ->update([
                'approval_status' => $request['status'],
                'modified_by' => $request['modified_by']
            ]);
        $this->workers->whereIn('id', $request['workers'])
            ->update([
                'directrecruitment_status' => $request['status'],
                'modified_by' => $request['modified_by']
            ]);
    }

    /**
     * Updates the calling visa status of an application.
     *
     * @param array $request The data needed for updating the calling visa status.
     *  - application_id (int) The ID of the application.
     *  - onboarding_country_id (int) The ID of the onboarding country.
     *  - modified_by (int) The ID of the user modifying the status.
     *
     * @return void
     */
    public function updateCallingVisaStatus($request)
    {
        $this->directRecruitmentCallingVisaStatus->where([
            'application_id' => $request['application_id'],
            'onboarding_country_id' => $request['onboarding_country_id']
        ])->update(['updated_on' => Carbon::now(), 'modified_by' => $request['modified_by']]);
    }

    /**
     * Validates the given request using the status validation rules.
     *
     * @param array $request The request data to be validated.
     *
     * @return array|null Returns an array with the error messages if validation fails,
     *                  otherwise returns null.
     */
    private function validateStatus(array $request): ?array
    {
        $validator = Validator::make($request, $this->statusValidation());
        if ($validator->fails()) {
            return ['error' => $validator->errors()];
        }

        return null;
    }

    /**
     * Check if worker data is present in the given request.
     *
     * @param array $request The request data.
     *
     * @return bool Returns true if worker data is present, false otherwise.
     */
    private function isWorkerDataPresent(array $request): bool
    {
        return !empty($request['workers']);
    }

    /**
     * Carry out worker checks.
     *
     * @param array $request The request data.
     * @return array|null Returns an array if there are validation errors, otherwise null.
     */
    private function carryOutWorkerChecks(array $request): ?array
    {
        if ($this->invalidWorkerCompanyCount($request)) {
            return ['InvalidUser' => true];
        }

        if ($this->isInvalidApplication($request)) {
            return ['InvalidUser' => true];
        }

        if ($this->isInvalidVisaProcessedCount($request)) {
            return ['visaReferenceNumberCountError' => true];
        }

        return null;
    }

    /**
     * Check if the provided worker company count is invalid.
     *
     * @param array $request The request array containing the workers and company ID.
     * @return bool Returns true if the worker company count is invalid, false otherwise.
     */
    private function invalidWorkerCompanyCount(array $request): bool
    {
        $workerCompanyCount = $this->workers->whereIn('id', $request['workers'])
            ->where('company_id', $request['company_id'])
            ->count();
        return $workerCompanyCount != count($request['workers']);
    }

    /**
     * Checks if the application is invalid based on company ID, onboarding country ID and application ID.
     *
     * @param array $request The request data including company ID, onboarding country ID and application ID.
     * @return bool Returns true if the application is invalid, false otherwise.
     */
    private function isInvalidApplication(array $request): bool
    {
        $applicationCheck = $this->checkForApplication($request['company_id'], $request['onboarding_country_id']);
        return is_null($applicationCheck) || ($applicationCheck->application_id != $request['application_id']);
    }

    /**
     * Check if the processed visa count is invalid.
     *
     * @param array $request The request data containing the processed visa references.
     *
     * @return bool Returns true if the processed visa count is invalid, false otherwise.
     */
    private function isInvalidVisaProcessedCount(array $request): bool
    {
        $processedVisaReferences = $this->getProcessedVisaReferences($request);
        return count($processedVisaReferences) != 1;
    }

    /**
     * Get the array of processed visa references.
     *
     * @param array $request The request data.
     *
     * @return array The array of processed visa references.
     */
    private function getProcessedVisaReferences(array $request): array
    {
        return $this->workerInsuranceDetails
            ->leftJoin('worker_visa', 'worker_visa.worker_id', 'worker_insurance_details.worker_id')
            ->whereIn('worker_insurance_details.worker_id', $request['workers'])
            ->where('worker_insurance_details.insurance_status', 'Purchased')
            ->select('worker_visa.calling_visa_reference_number')
            ->groupBy('worker_visa.calling_visa_reference_number')
            ->get()->toArray();
    }

    /**
     * Approves a request.
     *
     * @param array $request The request data.
     * @return array|null Returns an array with an error message if the validation fails or null if the request is approved.
     */
    private function approveRequest(array $request): ?array
    {
        $validator = Validator::make($request, $this->createValidation());
        if ($validator->fails()) {
            return ['error' => $validator->errors()];
        }

        $this->updateWorkerAndVisaRecordsForApproval($request);
        $this->updateQuotasBasedOnKsmReference($request);
        return null;
    }

    /**
     * Update worker and visa records for approval.
     *
     * @param array $request The request data containing worker and visa details
     *  The request array should have the following structure:
     *      - calling_visa_generated (string): The calling visa generated value
     *      - calling_visa_valid_until (string): The calling visa validity date
     *      - remarks (string): Any remarks or comments
     *      - status (string): Approval status
     *      - modified_by (string): The user who modified the records
     *      - workers (array): An array of worker IDs to update their records
     *
     * @return void
     */
    private function updateWorkerAndVisaRecordsForApproval(array $request): void
    {
        $updateData = ['calling_visa_generated' => $request['calling_visa_generated'], 'calling_visa_valid_until' => $request['calling_visa_valid_until'], 'remarks' => $request['remarks'], 'approval_status' => $request['status'], 'modified_by' => $request['modified_by']];
        $this->workerVisa->whereIn('worker_id', $request['workers'])->update($updateData);

        $this->workers->whereIn('id', $request['workers'])
            ->update([
                'directrecruitment_status' => 'Accepted',
                'modified_by' => $request['modified_by']
            ]);
    }

    /**
     * Update quotas based on KSM reference.
     *
     * @param array $request
     * @return void
     */
    private function updateQuotasBasedOnKsmReference($request)
    {
        foreach ($request['workers'] as $worker) {
            $ksmDetails = $this->workerVisa->where('worker_id', $worker)->first(['ksm_reference_number']);
            $workerDetails[$worker] = $ksmDetails->ksm_reference_number;
        }
        $ksmCount = array_count_values($workerDetails);
        foreach ($ksmCount as $key => $value) {
            event(new KSMQuotaUpdated($request['onboarding_country_id'], $key, $value, 'increment'));
        }
        // update utilised quota in onboarding country
        event(new WorkerQuotaUpdated($request['onboarding_country_id'], count($request['workers']), 'increment'));
    }

    /**
     * Returns a list of workers based on the provided request parameters.
     *
     * @param array $request An array containing the request parameters.
     *   - search: The search keyword to filter workers by. (optional)
     *   - export: A flag indicating whether to select workers for export. (optional)
     *
     * @return array|\Illuminate\Support\Collection|LengthAwarePaginator The list of workers based on the provided request parameters.
     *   - If the search parameter is provided and fails validation, an array will be returned with the 'error' key containing the validation errors.
     *   - If the export parameter is provided, the list of workers will be selected for export.
     *   - Otherwise, the list of workers will be selected for regular display.
     */
    // Keep constants at the top of the file or class for visibility.

    public function workersList($request)
    {
        if (!empty($request['search'])) {
            $validator = Validator::make($request, ['required|min:3']);
            if ($validator->fails()) {
                return [
                    'error' => $validator->errors()
                ];
            }
        }

        $data = $this->buildWorkersQuery($request);

        if (!empty($request['export'])) {
            $data = $this->selectForExport($data);
        } else {
            $data = $this->selectForRegular($data);
        }

        return $data;
    }

    /**
     * Builds the query to fetch worker data based on the given request parameters.
     *
     * @param array $request The request parameters
     * @return Builder The built query
     */
    private function buildWorkersQuery($request)
    {
        return $this->workers
            ->leftJoin('worker_bio_medical', 'worker_bio_medical.worker_id', 'workers.id')
            ->leftJoin('worker_visa', 'worker_visa.worker_id', 'workers.id')
            ->leftJoin('worker_insurance_details', 'worker_insurance_details.worker_id', 'workers.id')
            ->leftjoin('directrecruitment_workers', 'directrecruitment_workers.worker_id', '=', 'workers.id')
            ->whereIn('workers.company_id', $request['company_id'])
            ->where(function ($query) use ($request) {
                if ($request['user']['user_type'] == 'Customer') {
                    $query->where('workers.crm_prospect_id', '=', $request['user']['reference_id']);
                }
            })
            ->where('worker_visa.approval_status', '!=', 'Approved')
            ->where([
                'directrecruitment_workers.application_id' => $request['application_id'],
                'directrecruitment_workers.onboarding_country_id' => $request['onboarding_country_id'],
                'workers.cancel_status' => 0,
                'worker_insurance_details.insurance_status' => 'Purchased'
            ])
            ->where(function ($query) use ($request) {
                if (!empty($request['search'])) {
                    $query->where('workers.name', 'like', '%' . $request['search'] . '%')
                        ->orWhere('worker_visa.ksm_reference_number', 'like', '%' . $request['search'] . '%')
                        ->orWhere('worker_visa.calling_visa_reference_number', 'like', '%' . $request['search'] . '%')
                        ->orWhere('workers.passport_number', 'like', '%' . $request['search'] . '%');
                }
            })
            ->where(function ($query) use ($request) {
                if (!empty($request['filter'])) {
                    $query->where('worker_visa.approval_status', $request['filter']);
                }
            })
            ->where(function ($query) use ($request) {
                if (!empty($request['agent_id'])) {
                    $query->where('directrecruitment_workers.agent_id', $request['agent_id']);
                }
            });
    }

    /**
     * Selects specific columns from the given data for export.
     *
     * @param $data - The data to select from.
     * @return \Illuminate\Support\Collection The selected data for export.
     */
    private function selectForExport($data)
    {
        return $data->select('workers.name', 'worker_visa.ksm_reference_number', 'workers.passport_number', 'worker_bio_medical.bio_medical_valid_until', 'worker_visa.calling_visa_reference_number', 'worker_insurance_details.ig_policy_number', 'worker_insurance_details.hospitalization_policy_number', 'worker_visa.approval_status')->distinct('workers.id')
            ->orderBy('workers.id', 'desc')
            ->get();
    }

    /**
     * Selects specific columns from the given data and returns paginated results.
     *
     * @param Builder $data The data query builder instance.
     * @return LengthAwarePaginator The paginated list of selected records.
     */
    private function selectForRegular($data)
    {
        return $data->select('workers.id', 'workers.name', 'worker_visa.ksm_reference_number', 'workers.passport_number', 'worker_bio_medical.bio_medical_valid_until', 'directrecruitment_workers.application_id', 'directrecruitment_workers.onboarding_country_id', 'directrecruitment_workers.agent_id', 'worker_visa.calling_visa_reference_number', 'worker_visa.approval_status', 'worker_visa.calling_visa_generated', 'worker_visa.calling_visa_valid_until', 'worker_visa.remarks', 'worker_insurance_details.ig_policy_number', 'worker_insurance_details.hospitalization_policy_number')->distinct('workers.id')
            ->orderBy('workers.id', 'desc')
            ->paginate(Config::get('services.paginate_worker_row'));
    }

    /**
     * Show detailed information about a worker
     *
     * @param array $request The worker ID and company ID to retrieve the information
     * @return Collection
     */
    public function show($request)
    {
        return $this->workers->with(['workerBioMedical' => function ($query) {
            $query->select(['id', 'worker_id', 'bio_medical_valid_until']);
        }])->with(['workerVisa' => function ($query) {
            $query->select(['id', 'worker_id', 'ksm_reference_number', 'calling_visa_reference_number', 'approval_status', 'calling_visa_generated', 'calling_visa_valid_until', 'remarks']);
        }])->where('workers.id', $request['worker_id'])
            ->whereIn('company_id', $request['company_id'])
            ->select('id', 'name', 'passport_number')
            ->get();
    }

    /**
     * Check for application based on company ID and onboarding country ID.
     *
     * @param int $companyId The ID of the company.
     * @param int $onboardingCountryId
     * @return mixed The result of the query to find the onboarding country.
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
