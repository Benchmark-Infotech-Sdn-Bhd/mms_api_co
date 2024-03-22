<?php

namespace App\Services;

use App\Models\DirectRecruitmentCallingVisaStatus;
use App\Models\Workers;
use App\Models\WorkerImmigration;
use App\Models\WorkerImmigrationAttachments;
use App\Models\WorkerVisa;
use App\Models\WorkerInsuranceDetails;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\DirectRecruitmentOnboardingCountry;

class DirectRecruitmentImmigrationFeePaidServices
{
    /**
     * @var directRecruitmentCallingVisaStatus
     */
    private DirectRecruitmentCallingVisaStatus $directRecruitmentCallingVisaStatus;
    /**
     * @var Workers
     */
    private Workers $workers;

    /**
     * @var WorkerImmigration
     */
    private WorkerImmigration $workerImmigration;

    /**
     * @var WorkerImmigrationAttachments
     */
    private WorkerImmigrationAttachments $workerImmigrationAttachments;
    /**
     * @var WorkerVisa
     */
    private WorkerVisa $workerVisa;
    /**
     * @var WorkerInsuranceDetails
     */
    private WorkerInsuranceDetails $workerInsuranceDetails;
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
     * DirectRecruitmentImmigrationFeePaidServices constructor.
     * @param DirectRecruitmentCallingVisaStatus $directRecruitmentCallingVisaStatus
     * @param Workers $workers
     * @param WorkerImmigration $workerImmigration
     * @param WorkerImmigrationAttachments $workerImmigrationAttachments
     * @param WorkerVisa $workerVisa
     * @param WorkerInsuranceDetails $workerInsuranceDetails
     * @param Storage $storage ;
     * @param DirectRecruitmentExpensesServices $directRecruitmentExpensesServices
     * @param DirectRecruitmentOnboardingCountry $directRecruitmentOnboardingCountry
     */
    public function __construct(
        DirectRecruitmentCallingVisaStatus $directRecruitmentCallingVisaStatus,
        Workers                            $workers,
        WorkerImmigration                  $workerImmigration,
        WorkerImmigrationAttachments       $workerImmigrationAttachments,
        WorkerVisa                         $workerVisa,
        WorkerInsuranceDetails             $workerInsuranceDetails,
        Storage                            $storage,
        DirectRecruitmentExpensesServices  $directRecruitmentExpensesServices,
        DirectRecruitmentOnboardingCountry $directRecruitmentOnboardingCountry)
    {
        $this->directRecruitmentCallingVisaStatus = $directRecruitmentCallingVisaStatus;
        $this->workers = $workers;
        $this->workerImmigration = $workerImmigration;
        $this->workerImmigrationAttachments = $workerImmigrationAttachments;
        $this->workerVisa = $workerVisa;
        $this->workerInsuranceDetails = $workerInsuranceDetails;
        $this->storage = $storage;
        $this->directRecruitmentExpensesServices = $directRecruitmentExpensesServices;
        $this->directRecruitmentOnboardingCountry = $directRecruitmentOnboardingCountry;
    }

    /**
     * @return array
     */
    public function updateValidation(): array
    {
        return
            [
                'application_id' => 'required',
                'onboarding_country_id' => 'required',
                'total_fee' => 'required|regex:/^(([0-9]{0,6}+)(\.([0-9]{0,2}+))?)$/',
                'immigration_reference_number' => 'required',
                'payment_date' => 'required|date|date_format:Y-m-d'
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
     * Update the resource.
     *
     * @param mixed $request The request data for updating the resource.
     * @return bool|array Returns either a boolean value indicating success or an array with errors on validation failure.
     */
    public function update($request)
    {
        return $this->validateRequest($request) ?: $this->handleUpdateRequest($request);
    }

    /**
     * Validate the given request.
     *
     * @param Request $request The request to be validated.
     * @return array|null If validation fails, it returns an array with error details. Otherwise, it returns null.
     */
    private function validateRequest($request)
    {
        $validator = Validator::make($request->toArray(), $this->updateValidation());
        if ($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }

        $workers = explode(",", $request['workers']);
        $workerCompanyCount = $this->workers->whereIn('id', $workers)
            ->where('company_id', $request['company_id'])
            ->count();
        if ($workerCompanyCount != count($workers) || !$this->validApplication($request)) {
            return [
                'InvalidUser' => true
            ];
        }

        return null;
    }

    /**
     * Checks if the provided application is valid for the given request.
     *
     * @param $request - The request data.
     *     - 'company_id' (int) The company ID.
     *     - 'onboarding_country_id' (int) The onboarding country ID.
     *     - 'application_id' (int) The application ID.
     *
     * @return bool Returns true if the application is valid, false otherwise.
     */
    private function validApplication($request)
    {
        $applicationCheck = $this->checkForApplication($request['company_id'], $request['onboarding_country_id']);
        return !is_null($applicationCheck) && $applicationCheck->application_id == $request['application_id'];
    }

    /**
     * Handles the update request.
     *
     * @param mixed $request The request data.
     *
     * @return bool Returns true if the update request was handled successfully, false otherwise.
     */
    private function handleUpdateRequest($request)
    {
        if (!empty($request['workers'])) {
            list($fileName, $fileUrl) = $this->storeAttachments($request);
            if ($this->updateOrCreateImmigrationDetails($request, $fileName, $fileUrl)) {
                // ADD OTHER EXPENSES
                $request['expenses_application_id'] = $request['application_id'] ?? 0;
                $request['expenses_title'] = Config::get('services.OTHER_EXPENSES_TITLE')[4];
                $request['expenses_payment_reference_number'] = $request['immigration_reference_number'] ?? '';
                $request['expenses_payment_date'] = $request['payment_date'] ?? '';
                $request['expenses_amount'] = $request['total_fee'] ?? 0;
                $request['expenses_remarks'] = $request['remarks'] ?? '';
                $this->directRecruitmentExpensesServices->addOtherExpenses($request);
                return true;
            }
        }
        return false;
    }

    /**
     * Store attachments and return their file names and URLs.
     *
     * @param $request - The request object containing the uploaded files.
     * @return array An array containing the file names and URLs of the stored attachments.
     */
    private function storeAttachments($request)
    {
        $fileName = $fileUrl = '';
        if (request()->hasFile('attachment')) {
            foreach ($request->file('attachment') as $file) {
                $fileName = $file->getClientOriginalName();
                $filePath = '/directRecruitment/onboarding/immigrationFeePaid/' . $fileName;
                $this->storage::disk('linode')->put($filePath, file_get_contents($file));
                $fileUrl = $this->storage::disk('linode')->url($filePath);
            }
        }
        return [$fileName, $fileUrl];
    }

    /**
     * Update or create immigration details for multiple workers.
     *
     * @param array $request The request data containing worker details.
     * @param string $fileName The file name of the attachment.
     * @param string $fileUrl The file URL of the attachment.
     * @return Model The updated or created immigration details.
     */
    private function updateOrCreateImmigrationDetails($request, string $fileName, string $fileUrl)
    {
        $workers = explode(",", $request['workers']);
        foreach ($workers as $workerId) {
            $this->workerImmigration->updateOrCreate(
                ['worker_id' => $workerId],
                ['total_fee' => $request['total_fee'],
                    'immigration_reference_number' => $request['immigration_reference_number'],
                    'payment_date' => $request['payment_date'],
                    'immigration_status' => 'Paid',
                    'created_by' => $request['created_by'],
                    'modified_by' => $request['created_by']
                ]);

            if (!empty($fileName) && !empty($fileUrl)) {
                $this->workerImmigrationAttachments->updateOrCreate(
                    ['file_id' => $workerId],
                    ["file_name" => $fileName,
                        "file_type" => 'Immigration Fee Paid',
                        "file_url" => $fileUrl]);
            }
        }

        return $this->directRecruitmentCallingVisaStatus->where([
            'application_id' => $request['application_id'],
            'onboarding_country_id' => $request['onboarding_country_id']
        ])->update(['updated_on' => Carbon::now(), 'modified_by' => $request['created_by']]);
    }

    /**
     * Retrieve a list of workers based on the given request.
     *
     * @param array $request The request data including the following parameters:
     *  - company_id (array): The array of company IDs to filter the workers by.
     *  - user (array): The user data including the following parameters:
     *      - user_type (string): The type of the user (e.g. Customer).
     *      - reference_id: The reference ID of the user.
     *  - calling_visa_reference_number (mixed): The calling visa reference number to filter the workers by.
     *  - ksm_reference_number (mixed): The KSM reference number to filter the workers by.
     *
     * @return LengthAwarePaginator The paginated list of workers.
     */
    public function workersList($request)
    {
        return $this->workers
            ->leftJoin('worker_bio_medical', 'worker_bio_medical.worker_id', 'workers.id')
            ->leftJoin('worker_visa', 'worker_visa.worker_id', 'workers.id')
            ->leftJoin('worker_insurance_details', 'worker_insurance_details.worker_id', 'workers.id')
            ->whereIn('workers.company_id', $request['company_id'])
            ->where(function ($query) use ($request) {
                if ($request['user']['user_type'] == 'Customer') {
                    $query->where('workers.crm_prospect_id', '=', $request['user']['reference_id']);
                }
            })
            ->where('worker_visa.approval_status', 'Approved')
            ->where('worker_visa.calling_visa_reference_number', $request['calling_visa_reference_number'])
            ->where('worker_visa.ksm_reference_number', $request['ksm_reference_number'])
            ->where('workers.cancel_status', 0)
            ->select('workers.id', 'workers.name', 'workers.gender', 'worker_visa.ksm_reference_number', 'workers.passport_number', 'worker_bio_medical.bio_medical_valid_until')->distinct('workers.id')
            ->orderBy('workers.id', 'desc')
            ->paginate(Config::get('services.paginate_worker_row'));
    }

    /**
     * Returns a list of workers based on the given request parameters.
     *
     * @param array $request The request parameters.
     *                       - search: The search keyword (optional).
     *                       - export: Whether to export the workers (optional).
     *                       - application_id: The application ID (optional).
     *                       - onboarding_country_id: The onboarding country ID (optional).
     *                       - company_id: The company ID (optional).
     *
     * @return Collection|object|array The list of workers based on the given request parameters.
     *               - If the validation fails, returns an error message.
     *               - Otherwise, returns the worker details.
     */
    public function listBasedOnCallingVisa($request)
    {
        if (!empty($request['search'])) {
            $validator = Validator::make($request, $this->searchValidation());
            if ($validator->fails()) {
                return [
                    'error' => $validator->errors()
                ];
            }
        }

        $search = $request['search'];
        $export = $request['export'] ?? '';
        $applicationId = $request['application_id'];
        $onboardingCountryId = $request['onboarding_country_id'];
        $companyId = $request['company_id'];

        $workerDetails = $this->getBaseWorkerDetailsQuery($search, $applicationId, $onboardingCountryId, $companyId);

        if (!empty($export)) {
            $workerDetails = $this->getExportSpecificQuery($workerDetails);
        } else {
            $workerDetails = $this->getNonExportSpecificQuery($workerDetails);
        }

        return $workerDetails;
    }

    /**
     * Get the query to fetch base worker details.
     *
     * @param string $search The search keyword to filter the results.
     * @param int $applicationId The ID of the application.
     * @param int $onboardingCountryId The ID of the onboarding country.
     * @param int|array $companyId The ID or an array of IDs of the company/business.
     *
     * @return Builder The query builder instance.
     */
    private function getBaseWorkerDetailsQuery($search, $applicationId, $onboardingCountryId, $companyId)
    {
        return $this->workers
            ->leftJoin('worker_bio_medical', 'worker_bio_medical.worker_id', 'workers.id')
            ->leftJoin('worker_visa', 'worker_visa.worker_id', 'workers.id')
            ->leftJoin('worker_immigration', 'worker_immigration.worker_id', 'workers.id')
            ->leftjoin('directrecruitment_workers', 'directrecruitment_workers.worker_id', '=', 'workers.id')
            ->whereIn('workers.company_id', $companyId)
            ->where('worker_visa.approval_status', 'Approved')
            ->where('worker_immigration.immigration_status', null)
            ->where([
                'directrecruitment_workers.application_id' => $applicationId,
                'directrecruitment_workers.onboarding_country_id' => $onboardingCountryId,
                'workers.cancel_status' => 0
            ])
            ->where(function ($query) use ($search) {
                if (!empty($search)) {
                    $query->Where('worker_visa.ksm_reference_number', 'like', '%' . $search . '%')
                        ->orWhere('worker_visa.calling_visa_reference_number', 'like', '%' . $search . '%');
                }
            });
    }

    /**
     * Retrieves specific worker details for export.
     *
     * @param mixed $workerDetails The worker details object used for querying the database.
     * @return Collection
     */
    private function getExportSpecificQuery($workerDetails)
    {
        return $workerDetails->select('worker_visa.ksm_reference_number', 'worker_visa.calling_visa_reference_number', 'worker_visa.calling_visa_generated', 'worker_visa.calling_visa_valid_until', DB::raw('COUNT(workers.id) as workers'))
            ->selectRaw("(CASE WHEN (worker_immigration.immigration_status IS NULL) THEN 'Pending' ELSE worker_immigration.immigration_status END) as immigration_status")
            ->groupBy('worker_visa.ksm_reference_number', 'worker_visa.calling_visa_reference_number', 'worker_visa.calling_visa_generated', 'worker_visa.calling_visa_valid_until', 'worker_immigration.immigration_status')
            ->orderBy('worker_visa.calling_visa_valid_until', 'desc')
            ->get();
    }

    /**
     * Get the non-export specific query for worker details.
     *
     * @param object $workerDetails The worker details object.
     * @return object  The query result object.
     */
    private function getNonExportSpecificQuery($workerDetails)
    {
        return $workerDetails->select('worker_visa.ksm_reference_number', 'worker_visa.calling_visa_reference_number', 'worker_visa.calling_visa_generated', 'worker_visa.calling_visa_valid_until', 'worker_immigration.total_fee', 'worker_immigration.immigration_reference_number', 'worker_immigration.payment_date', 'worker_immigration.immigration_status', DB::raw('COUNT(workers.id) as workers'), DB::raw('GROUP_CONCAT(workers.id SEPARATOR ",") AS workers_id'))
            ->selectRaw("(CASE WHEN (worker_immigration.immigration_status IS NULL) THEN 'Pending' ELSE worker_immigration.immigration_status END) as immigration_status_value")
            ->groupBy('worker_visa.ksm_reference_number', 'worker_visa.calling_visa_reference_number', 'worker_visa.calling_visa_generated', 'worker_visa.calling_visa_valid_until', 'worker_immigration.total_fee', 'worker_immigration.immigration_reference_number', 'worker_immigration.payment_date', 'worker_immigration.immigration_status')
            ->orderBy('worker_visa.calling_visa_valid_until', 'desc')
            ->paginate(Config::get('services.paginate_worker_row'));
    }

    /**
     * Show method
     *
     * Retrieves information related to a specific request
     *
     * @param array $request The request information
     *     - calling_visa_reference_number: The reference number used to identify the request
     *     - company_id: An array of company IDs
     *
     * @return array Returns an array with the following elements:
     *     - process: Information related to the calling visa request, including 'calling_visa_reference_number' and 'submitted_on'
     *     - insurance: Information related to the insurance purchase for the calling visa request
     *     - approval: Information related to the calling visa approval, including 'calling_visa_generated' and 'calling_visa_valid_until'
     *     - If the user ID doesn't match the company ID, it will return ['InvalidUser' => true]
     */
    public function show($request)
    {
        $workerCheck = $this->getWorkerCheck($request['calling_visa_reference_number']);

        if (!in_array($workerCheck->company_id, $request['company_id'])) {
            return ['InvalidUser' => true];
        }

        $processCallingVisa = $this->getCallingVisa($request['calling_visa_reference_number'], ['calling_visa_reference_number', 'submitted_on']);
        $insurancePurchase = $this->getInsurancePurchase($request['calling_visa_reference_number']);
        $callingVisaApproval = $this->getCallingVisa($request['calling_visa_reference_number'], ['calling_visa_generated', 'calling_visa_valid_until']);

        return [
            'process' => $processCallingVisa,
            'insurance' => $insurancePurchase,
            'approval' => $callingVisaApproval
        ];
    }

    /**
     * Get the worker check data based on the calling visa reference number.
     *
     * @param string $callingVisaReferenceNumber The calling visa reference number to search for.
     * @return array|null Returns an array containing the worker check data or null if not found.
     */
    private function getWorkerCheck($callingVisaReferenceNumber)
    {
        return $this->workerVisa->leftJoin('workers', 'workers.id', 'worker_visa.worker_id')
            ->where('worker_visa.calling_visa_reference_number', $callingVisaReferenceNumber)
            ->first(['workers.company_id', 'worker_visa.calling_visa_reference_number', 'worker_visa.submitted_on']);
    }

    /**
     * Get the calling visa record based on the reference number.
     *
     * @param string $callingVisaReferenceNumber The reference number of the calling visa.
     * @param array $fields The fields to retrieve from the calling visa record.
     * @return mixed The calling visa record matching the reference number and specified fields.
     */
    private function getCallingVisa($callingVisaReferenceNumber, $fields)
    {
        return $this->workerVisa
            ->where('calling_visa_reference_number', $callingVisaReferenceNumber)
            ->first($fields);
    }

    /**
     * Retrieves the insurance purchase details for a given calling Visa reference number.
     *
     * @param string $callingVisaReferenceNumber The calling Visa reference number.
     *
     * @return Model|null The insurance purchase details, or null if not found.
     */
    private function getInsurancePurchase($callingVisaReferenceNumber)
    {
        return $this->workerInsuranceDetails
            ->leftJoin('worker_visa', 'worker_visa.worker_id', 'worker_insurance_details.worker_id')
            ->leftJoin('vendors', 'vendors.id', 'worker_insurance_details.insurance_provider_id')
            ->where('worker_visa.calling_visa_reference_number', $callingVisaReferenceNumber)
            ->first(['worker_insurance_details.insurance_provider_id', 'worker_insurance_details.ig_amount', 'worker_insurance_details.ig_policy_number', 'worker_insurance_details.hospitalization_amount', 'worker_insurance_details.hospitalization_policy_number', 'worker_insurance_details.insurance_submitted_on', 'worker_insurance_details.insurance_expiry_date', 'vendors.name as insurance_provider_name']);
    }

    /**
     * Check if there is an application for the given company and onboarding country.
     *
     * @param int $companyId The ID of the company.
     * @param int $onboardingCountryId The ID of the onboarding country.
     * @return mixed|null Returns the onboarding country details if an application is found, or null if not found.
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
