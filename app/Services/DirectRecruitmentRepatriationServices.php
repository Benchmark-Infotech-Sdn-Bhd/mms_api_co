<?php

namespace App\Services;

use App\Models\DirectRecruitmentPostArrivalStatus;
use App\Models\WorkerRepatriation;
use App\Models\WorkerRepatriationAttachments;
use App\Models\Workers;
use App\Models\WorkerVisa;
use App\Models\DirectRecruitmentOnboardingCountry;
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
        WorkerRepatriation $workerRepatriation, 
        WorkerRepatriationAttachments $workerRepatriationAttachments, 
        Workers $workers, 
        Storage $storage, 
        DirectRecruitmentExpensesServices $directRecruitmentExpensesServices, 
        WorkerVisa $workerVisa, 
        DirectRecruitmentOnboardingCountry $directRecruitmentOnboardingCountry
    )
    {
        $this->directRecruitmentPostArrivalStatus           = $directRecruitmentPostArrivalStatus;
        $this->workerRepatriation                           = $workerRepatriation;
        $this->workerRepatriationAttachments                = $workerRepatriationAttachments;
        $this->workers                                      = $workers;
        $this->storage                                      = $storage;
        $this->directRecruitmentExpensesServices            = $directRecruitmentExpensesServices;
        $this->workerVisa                                   = $workerVisa;
        $this->directRecruitmentOnboardingCountry           = $directRecruitmentOnboardingCountry;
    }
    /**
     * Perform search validation. Validates the input and returns the errors if validation fails.
     *
     * @return array The validation error messages if validation fails, otherwise false.
     */
    public function searchValidation(): array
    {
        return [
            'search' => 'required|min:3'
        ];
    }
    /**
     * Returns the validation rules for the create action.
     *
     * @return array The validation rules for the create action.
     *
     * The returned array has the following structure:
     * [
     *     'flight_number' => 'required|regex:/^[a-zA-Z0-9]*$/',
     *     'flight_date' => 'required|date|date_format:Y-m-d|after:yesterday',
     *     'expenses' => 'required|regex:/^(([0-9]{0,6}+)(\.([0-9]{0,2}+))?)$/',
     *     'checkout_memo_reference_number' => 'required|regex:/^[0-9]*$/|max:23',
     *     'attachment.*' => 'mimes:jpeg,pdf,png|max:2048'
     * ]
     *
     * The 'flight_number' field is required, meaning it must be present in the request data. It allows only alpha numeric values
     * The 'flight_date' field is also required, meaning it must be present in the request data. It should be in Y-m-d format and not past date
     * The 'expenses' field is also required, meaning it must be present in the request data. It allows onlt float value with max digit 6,2 
     * The 'checkout_memo_reference_number' field is also required, meaning it must be present in the request data. IT allows numeric with max 23 digits
     * The 'attachment' If it is present, file type must be jpeg,pdf,png and max size is 2 MB in the request data.
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
     * @param $applicationId, $onboardingCountryId, $modifiedBy
     * @return void
     */
    /**
     * Update the post arrival status status    
     *
     * @param array $applicationId, $onboardingCountryId, $modifiedBy data to update the on post arrival status.
     * - country_id : country id from master
     * - onboarding_status : on boarding status for the created country
     * 
     * @return void Returns void 
     *  - This function will udpate the post arrival status of the worker
     */
    public function updatePostArrivalStatus($applicationId, $onboardingCountryId, $modifiedBy): void
    {
        $this->directRecruitmentPostArrivalStatus->where([
            'application_id' => $applicationId,
            'onboarding_country_id' => $onboardingCountryId
        ])->update(['updated_on' => Carbon::now(), 'modified_by' => $modifiedBy]);
    }
    /**
     * Returns a paginated list of direct recruitment workers on boarding country with their direct recruitment application details.
     *
     * @param array $request The request data containing company id,  application_id, onboarding_country_id
     * @return mixed \Illuminate\Pagination\LengthAwarePaginator The paginated list of direct recruitment onboarding agent with direct recruitment application details.
     */    
    public function workersList($request): mixed
    {
        if(isset($request['search']) && !empty($request['search'])){
            $validator = Validator::make($request, $this->searchValidation());
            if($validator->fails()) {
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
            ->where(function ($query)use ($request) {
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
     * Get the count of workers in the given company id
     *
     * @param mixed $request
     * Return the worker count as interger return
     */
    public function checkWorkerCount(mixed $request): int
    {
        return $this->workers->whereIn('id', $request['workers'])
                ->where('company_id', $request[self::REQUEST_COMPANY_ID])
                ->count();
    }
    /**
     * Get the requested Application id belongs to the user company
     *
     * @param mixed $request
     * Return the worker count as interger return
     */
    public function checkApplication(mixed $request): object
    {
        return $this->directRecruitmentOnboardingCountry
                ->join('directrecruitment_applications', function ($join) use($request) {
                    $join->on('directrecruitment_onboarding_countries.application_id', '=', 'directrecruitment_applications.id')
                        ->where('directrecruitment_applications.company_id', $request[self::REQUEST_COMPANY_ID]);
                })->find($request[self::REQUEST_ONBOARDING_COUNTRY_ID]);
    }
    /**
     * Update the Worker Direct Recruitment Status Details.
     *
     * @param $workersId, $fomemaValidUntil, $modifiedBy
     * Updated the worker Direct Recruitment Status Details against the particular worker.
     */
    public function updateWorkerDirectRecruitmentStatus(array $workersId, date $fomemaValidUntil, int $modifiedBy): void
    {
        $this->workers->whereIn('id', $workersId)
        ->update([
            'directrecruitment_status' => self::DIRECT_RECRUITMENT_STATUS,
            'fomema_valid_until' => $fomemaValidUntil, 
            'modified_by' => $modifiedBy
        ]);
    } 
    /**
     * Update the Direct Recruitment worker Repatriation on the given input request.
     *
     * @param array $request The request data to update Direct Recruitment worker Repatriation.
     * - application_id: Direct recruitment application id
     * - onboarding_country_id: country id from master
     * - flight_number: Flight number travel by Worker
     * - flight_date: Flight travel date
     * - expenses: Expenses for the travel
     * - checkout_memo_reference_number: Checkout memo reference number
     * - workers: Workers to repatriate
     * - attachment: Refernce attachment file for repartiation
     * 
     * @return array|bool Returns array | bool with the following keys:
     *  - "validate": An array of validation errors, if any.
     *  - "isInvalidUser": A boolean returns true if user is invalid.
     *  - "isInvalidUser": A boolean returns true if user is invalid to access the record.
     *  - A boolean indicating if the Direct Recruitment worker Repatriation details successfully updated.
     */
    public function updateRepatriation($request): array|bool
    {
        $validator = Validator::make($request->toArray(), $this->createValidation());
        if($validator->fails()) {
            return [
                'error' => $validator->errors()
            ];
        }
        if(isset($request['workers']) && !empty($request['workers'])) {
            $request['workers'] = explode(',', $request['workers']);

            $workerCompanyCount = $this->checkWorkerCount($request);                                
            if($workerCompanyCount != count($request['workers'])) {
                return [
                    'InvalidUser' => true
                ];
            }

            $applicationCheck = $this->checkApplication($request);
            if(is_null($applicationCheck) || ($applicationCheck->application_id != $request[self::REQUEST_APPLICATION_ID])) {
                return [
                    'InvalidUser' => true
                ];
            }

            foreach ($request['workers'] as $workerId) {
                $this->createWorkerRepatriation($request, $workerId);

                if (request()->hasFile('attachment')) {
                    foreach($request->file('attachment') as $file) {
                        $this->uploadFiles($request->file('attachment'), $workerId);
                    }
                }
            }
            
            $this->updateWorkerDirectRecruitmentStatus($request['workers'], $request['fomema_valid_until'], $request['modified_by']);
            
            $workerDetails = [];
            $ksmCount = [];

            // update utilised quota based on ksm reference number
            foreach($request['workers'] as $worker) {
                $ksmDetails = $this->workerVisa->where('worker_id', $worker)->first(['ksm_reference_number']);
                $workerDetails[$worker] = $ksmDetails->ksm_reference_number;
            }
            $ksmCount = array_count_values($workerDetails);
            foreach($ksmCount as $key => $value) {
                event(new KSMQuotaUpdated($request[self::REQUEST_ONBOARDING_COUNTRY_ID], $key, $value, 'decrement'));
            }

            // update utilised quota in onboarding country
            event(new WorkerQuotaUpdated($request[self::REQUEST_ONBOARDING_COUNTRY_ID], count($request['workers']), 'decrement'));
        }
        $this->updatePostArrivalStatus($request[self::REQUEST_APPLICATION_ID], $request[self::REQUEST_ONBOARDING_COUNTRY_ID], $request['modified_by']);
        
        // ADD OTHER EXPENSES - Onboarding - Repatriation Expenses (RM)
        $this->addExpenses($request);
                
        return true;
    }
    /**
     * Create a new Worker Repatriation .
     *
     * @param array $request The data used to create the Direct Recruitment Arrival.
     * The array should contain the following keys:
     * - flight_number: Flight number 
     * - flight_date: Flight travel date
     * - expenses: Expenses for the travel
     * - checkout_memo_reference_number: Checkout memo reference number
     * - created_by: The user who created the Direct Recruitment Arrival.
     * - modified_by: The user who modified the Direct Recruitment Arrival.
     * 
     * @param int workerId: Repartiated Worker Id.
     *
     * @return void 
     */
    public function createWorkerRepatriation(array $request, int $workerId): void
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
     * Upload multiple files for worker Repatriation.
     *
     * @param array $files A array of files to be uploaded.
     * @param int $workerId The ID of the worker to associate the files with.
     *
     * @return void
     */
    public function uploadFiles($files, $workerId)
    {
        foreach ($files as $file) {
            $fileName = $file->getClientOriginalName();
            $filePath = 'directRecruitment/workers/repatriation/' . $workerId. '/'. $fileName; 

            $linode = $this->storage::disk('linode');
            $linode->put($filePath, file_get_contents($file));

            $fileUrl = $linode->url($filePath);

            $this->workerRepatriationAttachments->create([
                'file_id' => $workerId,
                'file_name' => $fileName,
                'file_type' => self::FILE_TYPE_REPATRIATION,
                'file_url' => $fileUrl,
                'created_by' => $request['modified_by'],
                'modified_by' => $request['modified_by']
            ]);                        
        }
    }
    /**
     * Add the Expenses details for Onboarding - Repatriation Expenses (RM)
     *
     * @param array $params The request data containing onboarding_attestation_id, embassy_attestation_id
     * @return void Inserted the new expense for OTHER EXPENSES - Onboarding - Attestation Costing.
     */
    public function addExpenses(array $request): void
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
        if(isset($request['search']) && !empty($request['search'])){
            $validator = Validator::make($request, $this->searchValidation());
            if($validator->fails()) {
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
            ->where(function ($query)use ($request) {
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
            ->select('workers.id', 'directrecruitment_workers.application_id', 'directrecruitment_workers.onboarding_country_id', 'workers.name', 'worker_visa.ksm_reference_number', 'workers.passport_number', 'worker_visa.entry_visa_valid_until','workers.date_of_birth', 'workers.gender', 'directrecruitment_workers.agent_id')
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
     * @param Builder $query The query builder to apply the search query to.
     * @param array $request The request containing the search parameter.
     * @return void
     */
    private function applySearchQuery($query, $request)
    {
        if(!empty($request['search'])) {
            $query->where('workers.name', 'like', '%'.$request['search'].'%')
            ->orWhere('worker_visa.ksm_reference_number', 'like', '%'.$request['search'].'%')
            ->orWhere('workers.passport_number', 'like', '%'.$request['search'].'%');
        }
    }
}