<?php

namespace App\Services;

use App\Models\DirectRecruitmentPostArrivalStatus;
use App\Models\WorkerFomema;
use App\Models\FOMEMAAttachment;
use App\Models\Workers;
use App\Models\DirectRecruitmentOnboardingCountry;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class DirectRecruitmentPostArrivalFomemaServices
{
    /**
     * @var DirectRecruitmentPostArrivalStatus
     */
    private DirectRecruitmentPostArrivalStatus $directRecruitmentPostArrivalStatus;
    /**
     * @var WorkerFomema
     */
    private WorkerFomema $workerFomema;
    /**
     * @var FOMEMAAttachment
     */
    private FOMEMAAttachment $fomemaAttachment;
    /**
     * @var workers
     */
    private Workers $workers;
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
     * DirectRecruitmentPostArrivalFomemaServices constructor.
     * @param DirectRecruitmentPostArrivalStatus $directRecruitmentPostArrivalStatus
     * @param WorkerFomema $workerFomema
     * @param FOMEMAAttachment $fomemaAttachment
     * @param Workers $workers
     * @param Storage $storage
     * @param DirectRecruitmentExpensesServices $directRecruitmentExpensesServices
     * @param DirectRecruitmentOnboardingCountry $directRecruitmentOnboardingCountry
     */
    public function __construct(
        DirectRecruitmentPostArrivalStatus $directRecruitmentPostArrivalStatus,
        WorkerFomema                       $workerFomema,
        FOMEMAAttachment                   $fomemaAttachment,
        Workers                            $workers,
        Storage                            $storage,
        DirectRecruitmentExpensesServices  $directRecruitmentExpensesServices,
        DirectRecruitmentOnboardingCountry $directRecruitmentOnboardingCountry
    )
    {
        $this->directRecruitmentPostArrivalStatus = $directRecruitmentPostArrivalStatus;
        $this->workerFomema = $workerFomema;
        $this->fomemaAttachment = $fomemaAttachment;
        $this->workers = $workers;
        $this->storage = $storage;
        $this->directRecruitmentExpensesServices = $directRecruitmentExpensesServices;
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
    public function plksShowValidation(): array
    {
        return [
            'id' => 'required'
        ];
    }

    /**
     * @return array
     */
    public function purchaseValidation(): array
    {
        return [
            'purchase_date' => 'required|date|date_format:Y-m-d|before:tomorrow',
            'fomema_total_charge' => 'required|regex:/^(([0-9]{0,6}+)(\.([0-9]{0,2}+))?)$/'
        ];
    }

    /**
     * @return array
     */
    public function fomemaFitValidation(): array
    {
        return [
            'clinic_name' => 'required|regex:/^[a-zA-Z ]*$/',
            'doctor_code' => 'required|regex:/^[a-zA-Z0-9]*$/',
            'allocated_xray' => 'required|regex:/^[a-zA-Z ]*$/',
            'xray_code' => 'required|regex:/^[a-zA-Z0-9]*$/',
            'fomema_valid_until' => 'required|date|date_format:Y-m-d|after:yesterday',
            'attachment.*' => 'mimes:jpeg,pdf,png|max:2048'
        ];
    }

    /**
     * @return array
     */
    public function fomemaUnfitValidation(): array
    {
        return [
            'attachment.*' => 'mimes:jpeg,pdf,png|max:2048'

        ];
    }

    /**
     * Update the post arrival status of an application for a specific onboarding country.
     *
     * @param int $applicationId The ID of the application.
     * @param int $onboardingCountryId The ID of the onboarding country.
     * @param int $modifiedBy The ID of the user who modified the status.
     *
     * @return void
     */
    public function updatePostArrivalStatus($applicationId, $onboardingCountryId, $modifiedBy)
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
     *     - search: The search keyword to filter workers by name, KSM reference number, or passport number (optional).
     *     - company_id: The array of company IDs to filter workers by (required).
     *     - user: The user information (required).
     *         - user_type: The type of user (Customer).
     *         - reference_id: The reference ID of the user.
     *     - application_id: The application ID (required).
     *     - onboarding_country_id: The onboarding country ID (required).
     * @return mixed The paginated list of workers with their associated information.
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
            ->leftJoin('worker_arrival', 'worker_arrival.worker_id', 'workers.id')
            ->leftJoin('worker_fomema', 'worker_fomema.worker_id', 'workers.id')
            ->leftJoin('directrecruitment_workers', 'directrecruitment_workers.worker_id', '=', 'workers.id')
            ->whereIn('workers.company_id', $request['company_id'])
            ->where(function ($query) use ($request) {
                if ($request['user']['user_type'] == 'Customer') {
                    $query->where('workers.crm_prospect_id', '=', $request['user']['reference_id']);
                }
            })
            ->where([
                'directrecruitment_workers.application_id' => $request['application_id'],
                'directrecruitment_workers.onboarding_country_id' => $request['onboarding_country_id'],
                'worker_arrival.arrival_status' => 'Arrived',
                'worker_fomema.fomema_status' => 'Pending'
            ])
            ->whereNotNull('worker_arrival.jtk_submitted_on')
            ->whereIn('workers.special_pass', [0, 2])
            ->where(function ($query) use ($request) {
                if (!empty($request['search'])) {
                    $query->where('workers.name', 'like', '%' . $request['search'] . '%')
                        ->orWhere('worker_visa.ksm_reference_number', 'like', '%' . $request['search'] . '%')
                        ->orWhere('workers.passport_number', 'like', '%' . $request['search'] . '%');
                }
            })
            ->select('workers.id', 'workers.name', 'worker_visa.ksm_reference_number', 'workers.passport_number', 'worker_visa.entry_visa_valid_until', 'directrecruitment_workers.application_id', 'directrecruitment_workers.onboarding_country_id', 'workers.special_pass_valid_until', 'worker_fomema.purchase_date', 'worker_fomema.fomema_status')->distinct('workers.id')
            ->orderBy('workers.id', 'desc')
            ->paginate(Config::get('services.paginate_worker_row'));
    }

    /**
     * Purchase a product or service based on the given request.
     *
     * @param array $request The request data containing the purchase details.
     *     The request array should have the following elements:
     *         - application_id: The ID of the application.
     *         - onboarding_country_id: The ID of the onboarding country.
     *         - modified_by: The ID of the user who modified the purchase.
     *         - workers: (optional) An array of worker details.
     *             If provided, the worker details should contain the following elements:
     *                 - worker_id: The ID of the worker.
     *                 - application_id: The ID of the application related to the worker.
     *                 - onboarding_country_id: The ID of the onboarding country related to the worker.
     *                 - modified_by: The ID of the user who modified the worker details.
     *
     * @return bool|array True if the purchase is successful, otherwise an array of validation errors.
     *     If the purchase fails due to validation errors, an array of validation errors will be returned.
     *     If the purchase is successful, true will be returned.
     */
    public function purchase($request)
    {
        $validationErrors = $this->validatePurchaseRequest($request);
        if ($validationErrors) return $validationErrors;

        if (!empty($request['workers'])) {
            if (!$this->validateWorkersAndApplication($request)) return ['InvalidUser' => true];

            $this->updateWorkerFomemaDetails($request);
        }

        $this->updatePostArrivalStatus(
            $request['application_id'],
            $request['onboarding_country_id'],
            $request['modified_by']
        );

        $this->directRecruitmentExpensesServices->addOtherExpenses($this->prepareExpenseData($request));

        return true;
    }

    /**
     * Validate the purchase request.
     *
     * @param array $request The purchase request data.
     * @return array|null Returns an array with error messages if the validation fails, otherwise returns null.
     */
    private function validatePurchaseRequest($request)
    {
        $validator = Validator::make($request, $this->purchaseValidation());
        if ($validator->fails()) {
            return ['error' => $validator->errors()];
        }

        return null;
    }

    /**
     * Validate workers and application.
     *
     * @param $request - The request data.
     *
     * @return bool Returns whether workers and application are valid.
     */
    private function validateWorkersAndApplication($request)
    {
        $workerCompanyCount = $this->workers->whereIn('id', $request['workers'])
            ->where('company_id', $request['company_id'])
            ->count();

        if ($workerCompanyCount != count($request['workers'])) {
            return false;
        }

        $applicationCheck = $this->checkForApplication($request['company_id'], $request['onboarding_country_id']);

        return !is_null($applicationCheck) || ($applicationCheck->application_id == $request['application_id']);
    }

    /**
     * Updates the Fomema details of workers.
     *
     * @param array $request The request data containing worker IDs and updated details.
     *
     * @return void
     */
    private function updateWorkerFomemaDetails($request)
    {
        $this->workerFomema->whereIn('worker_id', $request['workers'])
            ->update([
                'purchase_date' => $request['purchase_date'],
                'fomema_total_charge' => $request['fomema_total_charge'],
                'convenient_fee' => $request['convenient_fee'] ?? 3,
                'modified_by' => $request['modified_by']
            ]);
    }

    /**
     * Prepares the expense data based on the given request.
     *
     * @param array $request The request data.
     *
     * @return array The prepared expense data.
     */
    private function prepareExpenseData($request)
    {
        $expenseData = $request;
        $expenseData['expenses_application_id'] = $request['application_id'] ?? 0;
        $expenseData['expenses_title'] = Config::get('services.OTHER_EXPENSES_TITLE')[6];
        $expenseData['expenses_payment_reference_number'] = '';
        $expenseData['expenses_payment_date'] = $request['purchase_date'];
        $expenseData['expenses'] = $request['fomema_total_charge'] + ($request['convenient_fee'] ?? 3);
        $expenseData['expenses_amount'] = $request['expenses'] ?? 0;
        $expenseData['expenses_remarks'] = $request['remarks'] ?? '';

        return $expenseData;
    }

    /**
     * Perform Fomema Fit process for the given request.
     *
     * @param Request $request
     * @return array|true|true[] True on success, or an array containing errors if validation fails or invalid user
     */
    public function fomemaFit($request)
    {
        $validator = Validator::make($request->toArray(), $this->fomemaFitValidation());
        if ($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }
        if (!empty($request['workers'])) {
            $request['workers'] = explode(',', $request['workers']);
            $workerCompanyCount = $this->workers->whereIn('id', $request['workers'])
                ->where('company_id', $request['company_id'])
                ->count();
            if ($workerCompanyCount != count($request['workers'])) {
                return ['InvalidUser' => true];
            }
            $applicationCheck = $this->checkForApplication($request['company_id'], $request['onboarding_country_id']);
            if (is_null($applicationCheck) || ($applicationCheck->application_id != $request['application_id'])) {
                return ['InvalidUser' => true];
            }
            $this->workerFomema->whereIn('worker_id', $request['workers'])
                ->update([
                    'clinic_name' => $request['clinic_name'],
                    'doctor_code' => $request['doctor_code'],
                    'allocated_xray' => $request['allocated_xray'],
                    'xray_code' => $request['xray_code'],
                    'fomema_status' => 'Fit',
                    'modified_by' => $request['modified_by']
                ]);
            $this->workers->whereIn('id', $request['workers'])
                ->update([
                    'directrecruitment_status' => 'FOMEMA Fit',
                    'fomema_valid_until' => $request['fomema_valid_until'],
                    'modified_by' => $request['modified_by']
                ]);
            if (request()->hasFile('attachment')) {
                $this->handleAttachments($request['workers'], $request['modified_by'], $request->file('attachment'));
            }
        }
        $this->updatePostArrivalStatus($request['application_id'], $request['onboarding_country_id'], $request['modified_by']);
        return true;
    }

    /**
     * Handle attachments for workers.
     *
     * @param array $workerIds The array of worker IDs
     * @param string $modifiedBy The user who modified the attachments
     * @param array $files The array of files to handle
     *
     * @return void
     */
    private function handleAttachments(array $workerIds, string $modifiedBy, array $files)
    {
        foreach ($workerIds as $workerId) {
            foreach ($files as $file) {
                $fileName = $file->getClientOriginalName();
                $filePath = 'directRecruitment/workers/fomema/' . $workerId . '/' . $fileName;
                $linode = $this->storage::disk('linode');
                $linode->put($filePath, file_get_contents($file));
                $fileUrl = $this->storage::disk('linode')->url($filePath);
                $this->fomemaAttachment->create([
                    'file_id' => $workerId,
                    'file_name' => $fileName,
                    'file_type' => 'FOMEMA Fit',
                    'file_url' => $fileUrl,
                    'created_by' => $modifiedBy,
                    'modified_by' => $modifiedBy
                ]);
            }
        }
    }

    /**
     * Validate and process FOMEMA unfit request
     *
     * @param Request $request The HTTP request object
     * @return bool|array Returns true on successful processing or an array with error information on validation failure
     */
    public function fomemaUnfit(Request $request)
    {
        $validator = Validator::make($request->toArray(), $this->fomemaUnfitValidation());
        if ($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }
        $workersIds = explode(',', $request['workers']);

        if (!empty($workersIds)) {
            $this->validateWorkersAndApplication($request);
            $this->updateWorkerFomemaUnitDetails($workersIds, $request['modified_by']);
            $this->handleUnitAttachments($request, $workersIds, $request['modified_by']);
        }

        $this->updatePostArrivalStatus($request['application_id'], $request['onboarding_country_id'], $request['modified_by']);

        return true;
    }


    /**
     * Update Fomema details for multiple workers.
     *
     * @param array $workersIds
     * @param string $modifiedBy The username of the user who modified the details.
     * @return void
     */
    private function updateWorkerFomemaUnitDetails(array $workersIds, string $modifiedBy): void
    {
        $this->workerFomema->whereIn('worker_id', $workersIds)
            ->update([
                'fomema_status' => 'Unfit',
                'modified_by' => $modifiedBy
            ]);
    }

    /**
     * Handle unit attachments.
     *
     * @param Request $request The request object.
     * @param array $workersIds An array of worker IDs.
     * @param string $modifiedBy The name of the user who modified the attachments.
     *
     * @return void
     */
    private function handleUnitAttachments($request, array $workersIds, string $modifiedBy): void
    {
        if ($request->file('attachment')) {
            foreach ($workersIds as $workerId) {
                foreach ($request as $file) {
                    $this->persistAttachment($file, $workerId, $modifiedBy);
                }
            }
        }
    }

    /**
     * Persist the attachment file to storage and create a record in the fomema_attachment table.
     *
     * @param UploadedFile $file The attachment file to be persisted.
     * @param int $workerId The ID of the worker associated with the attachment.
     * @param string $modifiedBy The name of the user who modified the attachment.
     * @return void
     */
    private function persistAttachment($file, int $workerId, string $modifiedBy): void
    {
        $fileName = $file->getClientOriginalName();
        $filePath = 'directRecruitment/workers/fomema/' . $workerId . '/' . $fileName;
        $linode = $this->storage::disk('linode');
        $linode->put($filePath, file_get_contents($file));
        $fileUrl = $this->storage::disk('linode')->url($filePath);

        $this->fomemaAttachment->create([
            'file_id' => $workerId,
            'file_name' => $fileName,
            'file_type' => 'FOMEMA Unfit Letter',
            'file_url' => $fileUrl,
            'created_by' => $modifiedBy,
            'modified_by' => $modifiedBy
        ]);
    }

    /**
     * Update special pass for workers.
     *
     * @param array $request The request data.
     *      - $request['workers'] array The list of worker ids.
     *      - $request['company_id'] int The company id.
     *      - $request['onboarding_country_id'] int The onboarding country id.
     *      - $request['application_id'] int The application id.
     *      - $request['modified_by'] string The modified by user.
     *
     * @return array|bool If successful, returns true. If validation fails, returns an array with ['InvalidUser' => true].
     */
    public function updateSpecialPass($request): array|bool
    {
        if (!empty($request['workers'])) {
            $invalidUser = $this->updateWorkerSpecialPass($request['workers'], $request['company_id'], $request['onboarding_country_id'], $request['application_id'], $request['modified_by']);

            if ($invalidUser !== null) {
                return $invalidUser;
            }
        }

        $this->updatePostArrivalStatus($request['application_id'], $request['onboarding_country_id'], $request['modified_by']);

        return true;
    }

    /**
     * Updates the special pass flag of the given workers.
     *
     * @param array $workers An array of worker IDs to update.
     * @param int $companyId The ID of the company the workers belong to.
     * @param int $onboardingCountryId The ID of the onboarding country.
     * @param int $applicationId The ID of the application.
     * @param mixed $modifiedBy The value representing the modified by user.
     *
     * @return array|null Returns null if the update is successful, or an array with the key "InvalidUser" and value true if there are invalid users.
     */
    private function updateWorkerSpecialPass(array $workers, int $companyId, int $onboardingCountryId, int $applicationId, $modifiedBy): ?array
    {
        $workerCompanyCount = $this->workers->whereIn('id', $workers)
            ->where('company_id', $companyId)
            ->count();

        if ($workerCompanyCount != count($workers)) {
            return ['InvalidUser' => true];
        }

        $applicationCheck = $this->checkForApplication($companyId, $onboardingCountryId);

        if (is_null($applicationCheck) || ($applicationCheck->application_id != $applicationId)) {
            return ['InvalidUser' => true];
        }

        $this->workers->whereIn('id', $workers)
            ->update([
                'special_pass' => 1,
                'modified_by' => $modifiedBy
            ]);

        return null;
    }

    /**
     * Method to export the list of workers based on the given request.
     *
     * @param array $request The request data containing search and company_id information.
     *
     * @return mixed Returns the list of workers matching the criteria specified in the request,
     *               or an error array if the validation fails.
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
            ->leftJoin('worker_arrival', 'worker_arrival.worker_id', 'workers.id')
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
                'worker_arrival.arrival_status' => 'Arrived',
                'worker_fomema.fomema_status' => 'Pending'
            ])
            ->where(function ($query) use ($request) {
                if (!empty($request['search'])) {
                    $query->where('workers.name', 'like', '%' . $request['search'] . '%')
                        ->orWhere('worker_visa.ksm_reference_number', 'like', '%' . $request['search'] . '%')
                        ->orWhere('workers.passport_number', 'like', '%' . $request['search'] . '%');
                }
            })
            ->select('workers.id', 'workers.name', 'worker_visa.ksm_reference_number', 'workers.passport_number', 'worker_visa.entry_visa_valid_until', 'directrecruitment_workers.application_id', 'directrecruitment_workers.onboarding_country_id', 'workers.special_pass_valid_until', 'worker_fomema.purchase_date', 'worker_fomema.fomema_status')->distinct('workers.id')
            ->orderBy('workers.id', 'desc')
            ->get();
    }

    /**
     * Validate the request and retrieve the data for plksShow.
     *
     * @param array $request The request data.
     * @return mixed Returns an array containing the error message if validation fails, or the retrieved data.
     */
    public function plksShow($request): mixed
    {
        $validator = Validator::make($request, $this->plksShowValidation());
        if ($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }
        return $this->workers->with('workerFomema')
            ->with(['workerFomema' => function ($query) {
                $query->select('id', 'worker_id', 'fomema_total_charge', 'clinic_name', 'doctor_code', 'allocated_xray', 'xray_code');
            }])
            ->whereIn('company_id', $request['company_id'])
            ->select('id', 'name', 'fomema_valid_until')->find($request['id']);
    }

    /**
     * Check if there is an application for the given company and onboarding country
     *
     * @param int $companyId The ID of the company
     * @param int $onboardingCountryId The ID of the onboarding country
     * @return mixed|null The onboarding country information if an application exists, null otherwise
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
