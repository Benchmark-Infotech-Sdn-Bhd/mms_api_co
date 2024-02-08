<?php

namespace App\Services;

use App\Models\DirectRecruitmentCallingVisaStatus;
use App\Models\Workers;
use App\Models\WorkerVisa;
use App\Models\WorkerInsuranceDetails;
use App\Models\WorkerInsuranceAttachments;
use App\Models\Vendor;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Carbon\Carbon;
use Laravel\Lumen\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\DirectRecruitmentOnboardingCountry;

class DirectRecruitmentInsurancePurchaseServices
{
    /**
     * @var directRecruitmentCallingVisaStatus
     */
    private DirectRecruitmentCallingVisaStatus $directRecruitmentCallingVisaStatus;

    /**
     * @var workers
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
     * @var workerInsuranceAttachments
     */
    private WorkerInsuranceAttachments $workerInsuranceAttachments;
    /**
     * @var Vendor
     */
    private Vendor $vendor;

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
     * __construct method.
     *
     * This method is the constructor for the class.
     * It initializes class properties by assigning them with the provided dependencies.
     *
     * @param DirectRecruitmentCallingVisaStatus $directRecruitmentCallingVisaStatus The direct recruitment calling visa status object.
     * @param Workers $workers The workers object.
     * @param WorkerVisa $workerVisa The worker visa object.
     * @param WorkerInsuranceDetails $workerInsuranceDetails The worker insurance details object.
     * @param WorkerInsuranceAttachments $workerInsuranceAttachments The worker insurance attachments object.
     * @param Vendor $vendor The vendor object.
     * @param Storage $storage The storage object.
     * @param DirectRecruitmentExpensesServices $directRecruitmentExpensesServices The direct recruitment expenses services object.
     * @param DirectRecruitmentOnboardingCountry $directRecruitmentOnboardingCountry The direct recruitment onboarding country object.
     *
     * @return void
     */
    public function __construct(
        DirectRecruitmentCallingVisaStatus $directRecruitmentCallingVisaStatus,
        Workers                            $workers,
        WorkerVisa                         $workerVisa,
        WorkerInsuranceDetails             $workerInsuranceDetails,
        WorkerInsuranceAttachments         $workerInsuranceAttachments,
        Vendor                             $vendor,
        Storage                            $storage,
        DirectRecruitmentExpensesServices  $directRecruitmentExpensesServices,
        DirectRecruitmentOnboardingCountry $directRecruitmentOnboardingCountry
    )
    {
        $this->directRecruitmentCallingVisaStatus = $directRecruitmentCallingVisaStatus;
        $this->workers = $workers;
        $this->workerVisa = $workerVisa;
        $this->workerInsuranceDetails = $workerInsuranceDetails;
        $this->workerInsuranceAttachments = $workerInsuranceAttachments;
        $this->vendor = $vendor;
        $this->storage = $storage;
        $this->directRecruitmentExpensesServices = $directRecruitmentExpensesServices;
        $this->directRecruitmentOnboardingCountry = $directRecruitmentOnboardingCountry;
    }

    /**
     * Retrieve the list of workers based on the given request.
     *
     * @param array $request The request data.
     *     - search: The search keyword (optional).
     *     - company_id: The company ids to filter by.
     *     - user: The user details, including user_type and reference_id.
     *     - application_id: The application id to filter by.
     *     - onboarding_country_id: The onboarding country id to filter by.
     *     - export: Flag to indicate if export is enabled (optional).
     *
     * @return mixed The worker list.
     *     If export is enabled, it is an array of worker details:
     *     [
     *         [
     *             'name' => 'Worker Name',
     *             'ksm_reference_number' => 'KSM Reference Number',
     *             'passport_number' => 'Passport Number',
     *             'calling_visa_reference_number' => 'Calling Visa Reference Number',
     *             'insurance_status' => 'Insurance Status'
     *         ],
     *         ...
     *     ]
     *
     *     If export is not enabled, it is a paginated list of worker details:
     *     [
     *         [
     *             'id' => 'Worker ID',
     *             'name' => 'Worker Name',
     *             'ksm_reference_number' => 'KSM Reference Number',
     *             'passport_number' => 'Passport Number',
     *             'application_id' => 'Application ID',
     *             'onboarding_country_id' => 'Onboarding Country ID',
     *             'agent_id' => 'Agent ID',
     *             'calling_visa_reference_number' => 'Calling Visa Reference Number',
     *             'submitted_on' => 'Submitted On',
     *             'status' => 'Status',
     *             'ig_policy_number' => 'IG Policy Number',
     *             'hospitalization_policy_number' => 'Hospitalization Policy Number',
     *             'insurance_provider_id' => 'Insurance Provider ID',
     *             'ig_amount' => 'IG Amount',
     *             'hospitalization_amount' => 'Hospitalization Amount',
     *             'insurance_submitted_on' => 'Insurance Submitted On',
     *             'insurance_expiry_date' => 'Insurance Expiry Date',
     *             'insurance_status' => 'Insurance Status'
     *         ],
     *         ...
     *     ]
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
            ->join('worker_visa', function ($join) {
                $join->on('worker_visa.worker_id', '=', 'workers.id')
                    ->where('worker_visa.status', '=', 'Processed');
            })
            ->leftJoin('worker_insurance_details', 'worker_insurance_details.worker_id', 'worker_visa.worker_id')
            ->leftjoin('directrecruitment_workers', 'directrecruitment_workers.worker_id', '=', 'workers.id')
            ->whereIn('workers.company_id', $request['company_id'])
            ->where(function ($query) use ($request) {
                if ($request['user']['user_type'] == 'Customer') {
                    $query->where('workers.crm_prospect_id', '=', $request['user']['reference_id']);
                }
            })
            ->where('worker_insurance_details.insurance_status', 'Pending')
            ->where([
                ['directrecruitment_workers.application_id', $request['application_id']],
                ['directrecruitment_workers.onboarding_country_id', $request['onboarding_country_id']],
                ['workers.cancel_status', 0]
            ])
            ->where(function ($query) use ($request) {
                if (!empty($request['search'])) {
                    $query->where('workers.name', 'like', '%' . $request['search'] . '%')
                        ->orWhere('worker_visa.ksm_reference_number', 'like', '%' . $request['search'] . '%')
                        ->orWhere('workers.passport_number', 'like', '%' . $request['search'] . '%')
                        ->orWhere('worker_visa.calling_visa_reference_number', 'like', '%' . $request['search'] . '%');
                }
            });
        if (!empty($request['export'])) {
            $data = $data->select('workers.name', 'worker_visa.ksm_reference_number', 'workers.passport_number', 'worker_visa.calling_visa_reference_number', 'worker_insurance_details.insurance_status')->distinct('workers.id')
                ->distinct('workers.id')
                ->orderBy('workers.id', 'DESC')
                ->get();
        } else {
            $data = $data->select('workers.id', 'workers.name', 'worker_visa.ksm_reference_number', 'workers.passport_number', 'directrecruitment_workers.application_id', 'directrecruitment_workers.onboarding_country_id', 'directrecruitment_workers.agent_id', 'worker_visa.calling_visa_reference_number', 'worker_visa.submitted_on', 'worker_visa.status', 'worker_insurance_details.ig_policy_number', 'worker_insurance_details.hospitalization_policy_number', 'worker_insurance_details.insurance_provider_id', 'worker_insurance_details.ig_amount', 'worker_insurance_details.hospitalization_amount', 'worker_insurance_details.insurance_submitted_on', 'worker_insurance_details.insurance_expiry_date', 'worker_insurance_details.insurance_status')->distinct('workers.id')
                ->distinct('workers.id')
                ->orderBy('workers.id', 'DESC')
                ->paginate(Config::get('services.paginate_worker_row'));
        }
        return $data;
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
     * Retrieves worker information based on the provided request.
     *
     * @param array $request The request object containing the following properties:
     *                      - id: The ID of the worker to fetch.
     *                      - company_id: An array of company IDs to filter by.
     *
     * @return Collection A collection of worker information.
     */
    public function show($request)
    {
        return $this->workers
            ->join('worker_visa', function ($join) {
                $join->on('worker_visa.worker_id', '=', 'workers.id')
                    ->where('worker_visa.status', '=', 'Processed');
            })
            ->leftJoin('worker_insurance_details', 'worker_insurance_details.worker_id', 'worker_visa.worker_id')
            ->where([
                ['workers.id', $request['id']]
            ])
            ->leftjoin('directrecruitment_workers', 'directrecruitment_workers.worker_id', '=', 'workers.id')
            ->whereIn('workers.company_id', $request['company_id'])
            ->select('workers.id', 'workers.name', 'worker_visa.ksm_reference_number', 'workers.passport_number', 'directrecruitment_workers.application_id', 'directrecruitment_workers.onboarding_country_id', 'directrecruitment_workers.agent_id', 'worker_visa.calling_visa_reference_number', 'worker_visa.submitted_on', 'worker_visa.status', 'worker_insurance_details.ig_policy_number', 'worker_insurance_details.hospitalization_policy_number', 'worker_insurance_details.insurance_provider_id', 'worker_insurance_details.ig_amount', 'worker_insurance_details.hospitalization_amount', 'worker_insurance_details.insurance_submitted_on', 'worker_insurance_details.insurance_expiry_date', 'worker_insurance_details.insurance_status')->distinct('workers.id')
            ->with(['workerInsuranceAttachments' => function ($query) {
                $query->orderBy('created_at', 'desc');
            }])
            ->distinct('workers.id')
            ->get();
    }

    /**
     * Submit the request data for processing.
     *
     * @param $request - The request data.
     * @return bool|array Returns true if the submission is successful, otherwise returns an array of error messages.
     */
    public function submit($request): bool|array
    {
        $params = $this->prepareParams($request);

        $validator = $this->validateRequest($request, $params);
        if ($validator !== true) {
            return $validator;
        }

        if (empty($request['workers'])) {
            return false;
        }

        return $this->processWorkers($request, $params);
    }

    /**
     * Prepare the parameters for further processing.
     *
     * @param Request $request The HTTP request object.
     * @return array The prepared parameters.
     */
    private function prepareParams($request): array
    {
        $params = $request->all();
        $user = JWTAuth::parseToken()->authenticate();
        $params['created_by'] = $user['id'];

        return $params;
    }

    /**
     * Performing validation of submitted request
     *
     * @param $request
     * @param $params
     * @return bool|array
     */
    private function validateRequest($request, $params): bool|array
    {
        $validator = Validator::make($request->toArray(), $this->submitValidation());
        if ($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }

        $invalidWorkersResponse = $this->validateWorkers($request, $params);
        if ($invalidWorkersResponse !== true) {
            return $invalidWorkersResponse;
        }

        return true; // return true if all validations are passed
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
                'ig_policy_number' => 'required',
                'hospitalization_policy_number' => 'required',
                'insurance_provider_id' => 'required',
                'ig_amount' => 'required|regex:/^(([0-9]{0,6}+)(\.([0-9]{0,2}+))?)$/',
                'hospitalization_amount' => 'required|regex:/^(([0-9]{0,6}+)(\.([0-9]{0,2}+))?)$/',
                'insurance_submitted_on' => 'required|date|date_format:Y-m-d|before:tomorrow',
                'insurance_expiry_date' => 'required|date|date_format:Y-m-d'
            ];
    }

    /**
     * Validating workers of submitted request
     *
     * @param $request
     * @param $params
     * @return bool|array
     */
    private function validateWorkers($request, $params): bool|array
    {
        $workers = explode(",", $request['workers']);
        $workerCompanyCount = $this->workers->whereIn('id', $workers)
            ->where('company_id', $params['company_id'])
            ->count();
        if ($workerCompanyCount != count($workers)) {
            return [
                'InvalidUser' => true
            ];
        }

        if (!$this->isApplicationValid($request)) {
            return [
                'InvalidUser' => true
            ];
        }

        return true; // return true if all workers are valid
    }

    /**
     * Checking if application is valid or not
     *
     * @param $request
     * @return bool
     */
    private function isApplicationValid($request): bool
    {
        $applicationCheck = $this->checkForApplication($request['company_id'], $request['onboarding_country_id']);
        return !is_null($applicationCheck) && $applicationCheck->application_id == $request['application_id'];
    }

    /**
     * This method checks if an application exists for a given company and onboarding country.
     *
     * @param int $companyId The ID of the company
     * @param int $onboardingCountryId The ID of the onboarding country
     * @return mixed|null Returns the found application or null if not found
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

    /**
     * Process the workers
     *
     * This method processes the workers by performing the following actions:
     * 1. Extracts the worker IDs from the request parameter 'workers'
     * 2. Queries the workerVisa table to retrieve the processed visa details for the given worker IDs
     * 3. Checks if there is only one calling visa reference number for the processed visas
     * 4. Validates that the number of workers selected matches the number of processed visas
     * 5. Processes any attached files
     * 6. Processes the worker details
     * 7. Updates the direct recruitment calling visa status record
     * 8. Adds other expenses for insurance and hospitalization
     *
     * @param array $request The request data
     * @param array $params Additional parameters
     * @return bool|array Returns true if the workers were processed successfully, otherwise returns an array with error details
     */
    private function processWorkers($request, $params): bool|array
    {
        $workers = explode(",", $request['workers']);
        if (is_array($workers)) {
            $processedVisa = $this->workerVisa
                ->whereIn('worker_id', $workers)
                ->where('status', 'Processed')
                ->select('calling_visa_reference_number')
                ->groupBy('calling_visa_reference_number')
                ->get()->toArray();
            if (count($processedVisa) == 1) {
                $visaCount = $this->workerVisa->where([
                    'status' => 'Processed',
                    'calling_visa_reference_number' => $workerVisaProcessed[0]['calling_visa_reference_number'] ?? ''
                ])->count('worker_id');
                if (count($workers) <> $visaCount) {
                    return ['workerCountError' => true];
                }
            } else {
                return ['visaReferenceNumberCountError' => true];
            }

            $fileName = '';
            $fileUrl = '';
            if (request()->hasFile('attachment')) {
                $attachments = $this->processAttachments($request->file('attachment'));
                $fileName = $attachments['fileName'];
                $fileUrl = $attachments['fileUrl'];
            }

            $this->processWorkerDetails($workers, $request, $params, $fileName, $fileUrl);

            $this->directRecruitmentCallingVisaStatus->where([
                'application_id' => $request['application_id'],
                'onboarding_country_id' => $request['onboarding_country_id'],
            ])->update(['updated_on' => Carbon::now(), 'modified_by' => $params['created_by']]);

            $insuranceSubmittedOn = $request['insurance_submitted_on'];
            $igAmount = $request['ig_amount'] ?? 0;
            $hospitalizationAmount = $request['hospitalization_amount'] ?? 0;

            // ADD OTHER EXPENSES - Onboarding - Calling Visa - I.G Insurance
            $this->addOtherExpenses($request, 3, $insuranceSubmittedOn, $igAmount);
            // ADD OTHER EXPENSES - Onboarding - Calling Visa - Hospitalisation
            $this->addOtherExpenses($request, 5, $insuranceSubmittedOn, $hospitalizationAmount);
        }
        return true;
    }

    /**
     * Process attachments and upload them.
     *
     * @param array $attachments The list of attachments to process.
     * @return array The compacted array containing the processed attachment information.
     */
    private function processAttachments($attachments)
    {
        $fileName = $fileUrl = '';
        foreach ($attachments as $file) {
            $fileName = $file->getClientOriginalName();
            $fileUrl = $this->uploadFile($file, $fileName);
        }
        return compact('fileName', 'fileUrl');
    }

    /**
     * Uploads a file to the Linode storage and returns the URL of the uploaded file.
     *
     * @param string $file The path of the file to be uploaded.
     * @param string $fileName The name to be given to the uploaded file.
     * @return string The URL of the uploaded file.
     */
    private function uploadFile($file, $fileName)
    {
        $filePath = '/directRecruitment/onboarding/insurancePurchase/' . $fileName;
        $linode = $this->storage::disk('linode');
        $linode->put($filePath, file_get_contents($file));
        return $this->storage::disk('linode')->url($filePath);
    }

    /**
     * Process worker details and update insurance information and attachments.
     *
     * @param array $workers The array of worker IDs.
     * @param array $request The request data.
     * @param array $params The parameters data.
     * @param string $fileName The name of the insurance attachment file.
     * @param string $fileUrl The URL of the insurance attachment file.
     *
     * @return void
     */
    private function processWorkerDetails($workers, $request, $params, $fileName, $fileUrl)
    {
        foreach ($workers as $workerId) {
            $this->updateInsuranceDetails($request, $params, $workerId);

            if (!empty($fileName) && !empty($fileUrl)) {
                $this->updateInsuranceAttachments($workerId, $fileName, $fileUrl);
            }
        }
    }

    /**
     * Updates or creates insurance details for a worker.
     *
     * @param array $request The request data containing insurance details.
     * @param array $params Additional parameters.
     * @param int $workerId The ID of the worker.
     * @return void
     */
    private function updateInsuranceDetails($request, $params, $workerId)
    {
        $this->workerInsuranceDetails->updateOrCreate(
            ['worker_id' => $workerId],
            ['ig_policy_number' => $request['ig_policy_number'],
                'hospitalization_policy_number' => $request['hospitalization_policy_number'],
                'insurance_provider_id' => $request['insurance_provider_id'],
                'ig_amount' => $request['ig_amount'],
                'hospitalization_amount' => $request['hospitalization_amount'],
                'insurance_submitted_on' => $request['insurance_submitted_on'],
                'insurance_expiry_date' => $request['insurance_expiry_date'],
                'insurance_status' => 'Purchased',
                'created_by' => $params['created_by'],
                'modified_by' => $params['created_by']
            ]);
    }

    /**
     * Update or create insurance attachments for a worker.
     *
     * @param int $workerId The ID of the worker.
     * @param string $fileName The name of the file.
     * @param string $fileUrl The URL of the file.
     * @return void
     */
    private function updateInsuranceAttachments($workerId, $fileName, $fileUrl)
    {
        $this->workerInsuranceAttachments->updateOrCreate(
            ['file_id' => $workerId],
            ["file_name" => $fileName,
                "file_type" => 'Insurance Purchase',
                "file_url" => $fileUrl
            ]);
    }

    /**
     * Add other expenses to the request array.
     *
     * @param array $request The request array.
     * @param int $expenseIndex The index of the expense.
     * @param mixed $paymentDate The payment date.
     * @param mixed $amount The expense amount.
     * @return void
     */
    private function addOtherExpenses(&$request, int $expenseIndex, $paymentDate, $amount)
    {
        $request['expenses_application_id'] = $request['application_id'] ?? 0;
        $request['expenses_title'] = Config::get('services.OTHER_EXPENSES_TITLE')[$expenseIndex];
        $request['expenses_payment_reference_number'] = '';
        $request['expenses_payment_date'] = $paymentDate;
        $request['expenses_amount'] = $amount;
        $request['expenses_remarks'] = $request['remarks'] ?? '';
        $this->directRecruitmentExpensesServices->addOtherExpenses($request);
    }

    /**
     * @param $request
     * @return mixed
     */
    public function insuranceProviderDropDown($request): mixed
    {
        return $this->vendor
            ->where('type', 'Insurance')
            ->where('company_id', $request['company_id'])
            ->select('id', 'name')
            ->distinct('id')
            ->get();
    }
}
